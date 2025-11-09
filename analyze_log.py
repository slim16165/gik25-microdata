#!/usr/bin/env python3
"""
Script per analizzare file di log Apache/WordPress usando pattern matching
Simile a Drain log parser - analizza errori comuni
"""

import re
import os
from collections import defaultdict
from datetime import datetime
from pathlib import Path

# Pattern di errori da cercare
ERROR_PATTERNS = {
    'PHP Fatal Error': [
        r'PHP Fatal error',
        r'PHP Parse error',
        r'Uncaught Error',
        r'Uncaught Exception',
    ],
    'PHP Warning': [
        r'PHP Warning',
        r'PHP Notice',
    ],
    'WordPress Database Error': [
        r'WordPress database error',
        r'Table.*doesn\'t exist',
        r'Table.*does not exist',
    ],
    'Foreach Type Error': [
        r'foreach\(\) argument must be of type array\|object',
        r'foreach\(\) argument must be of type array',
    ],
    'Callback Error': [
        r'call_user_func_array\(\): Argument #1.*must be a valid callback',
        r'must be a valid callback',
    ],
    'Apache Error': [
        r'AH01071',
        r'AH\d+',
    ],
    'Maximum Execution Time': [
        r'Maximum execution time',
        r'maximum execution time exceeded',
    ],
    'Memory Error': [
        r'Allowed memory size',
        r'Fatal error: Allowed memory size',
    ],
    'File Not Found': [
        r'Failed to open stream: No such file or directory',
        r'include.*failed to open stream',
    ],
}

# Pattern per ignorare (errori noti non critici)
IGNORE_PATTERNS = [
    r'Table.*actionscheduler.*doesn\'t exist',
    r'Table.*actionscheduler.*does not exist',
    r'ActionScheduler.*Table.*doesn\'t exist',
]

def should_ignore(line):
    """Verifica se una riga dovrebbe essere ignorata"""
    for pattern in IGNORE_PATTERNS:
        if re.search(pattern, line, re.IGNORECASE):
            return True
    return False

def extract_context(line):
    """Estrae il contesto di esecuzione da una riga"""
    context = 'unknown'
    
    if 'wp-cli' in line.lower() or 'WP_CLI' in line:
        context = 'wp_cli'
    elif 'admin-ajax.php' in line:
        context = 'ajax'
    elif 'wp-cron.php' in line:
        context = 'wp_cron'
    elif '/wp-admin/' in line:
        context = 'backend'
    elif '/wp-json/' in line or 'REST API' in line:
        context = 'rest_api'
    elif 'wp-blog-header.php' in line or 'template-loader.php' in line:
        context = 'frontend'
    
    return context

def parse_timestamp(line):
    """Cerca di estrarre un timestamp dalla riga"""
    # Pattern comuni per timestamp
    patterns = [
        r'(\d{4}[-/]\d{2}[-/]\d{2} \d{2}:\d{2}:\d{2})',
        r'\[(\d{2}/\w{3}/\d{4}:\d{2}:\d{2}:\d{2})',
    ]
    
    for pattern in patterns:
        match = re.search(pattern, line)
        if match:
            try:
                ts_str = match.group(1)
                # Prova vari formati
                for fmt in ['%Y-%m-%d %H:%M:%S', '%Y/%m/%d %H:%M:%S', '%d/%b/%Y:%H:%M:%S']:
                    try:
                        return datetime.strptime(ts_str, fmt)
                    except:
                        continue
            except:
                pass
    return None

def read_log_tail(file_path, max_lines=10000, chunk_size=5*1024*1024):
    """Legge le ultime righe di un file (efficiente per file grandi)"""
    try:
        file_size = os.path.getsize(file_path)
        
        if file_size > 100 * 1024 * 1024:  # > 100MB
            print(f"[!] File molto grande ({file_size / 1024 / 1024:.1f} MB), leggerò solo gli ultimi {chunk_size / 1024 / 1024:.1f} MB")
        
        with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
            # Per file grandi, vai alla fine
            if file_size > chunk_size:
                f.seek(max(0, file_size - chunk_size))
                # Salta la prima riga (probabilmente incompleta)
                f.readline()
            
            lines = []
            for line in f:
                line = line.strip()
                if line:
                    lines.append(line)
            
            # Restituisci solo le ultime max_lines
            return lines[-max_lines:] if len(lines) > max_lines else lines
            
    except Exception as e:
        print(f"[-] Errore nella lettura del file: {e}")
        return []

def analyze_log_file(file_path, max_lines=10000):
    """Analizza un file di log e trova errori"""
    
    if not os.path.exists(file_path):
        return {
            'status': 'error',
            'message': f'File non trovato: {file_path}',
            'issues': []
        }
    
    print(f"[*] Lettura file: {file_path}")
    print(f"[*] Analisi ultime {max_lines} righe...")
    
    lines = read_log_tail(file_path, max_lines)
    
    if not lines:
        return {
            'status': 'warning',
            'message': 'Nessuna riga trovata nel file',
            'issues': []
        }
    
    print(f"[+] Letti {len(lines)} righe")
    print("[*] Analisi errori in corso...\n")
    
    # Raccogli errori per tipo
    error_counts = defaultdict(lambda: {
        'count': 0,
        'examples': [],
        'contexts': set(),
        'severity': 'warning'
    })
    
    # Analizza ogni riga
    for line in lines:
        # Ignora righe che dovrebbero essere ignorate
        if should_ignore(line):
            continue
        
        # Estrai contesto
        context = extract_context(line)
        
        # Cerca pattern di errori
        for error_type, patterns in ERROR_PATTERNS.items():
            for pattern in patterns:
                if re.search(pattern, line, re.IGNORECASE):
                    key = f"{error_type}:{pattern}"
                    
                    error_counts[key]['count'] += 1
                    error_counts[key]['contexts'].add(context)
                    
                    # Determina severity
                    if 'Fatal' in error_type or 'Uncaught' in error_type or 'Parse' in error_type:
                        error_counts[key]['severity'] = 'error'
                    elif 'Callback' in error_type or 'Foreach' in error_type:
                        error_counts[key]['severity'] = 'error'
                    
                    # Salva esempi (max 5) - prendi righe diverse
                    if len(error_counts[key]['examples']) < 5:
                        # Salva la riga completa (sarà troncata nella stampa)
                        # Evita duplicati esatti
                        if line not in error_counts[key]['examples']:
                            error_counts[key]['examples'].append(line)
                    
                    break  # Una riga può matchare solo un tipo di errore
    
    # Raggruppa per tipo di errore (senza pattern specifico)
    grouped_errors = defaultdict(lambda: {
        'count': 0,
        'examples': [],
        'contexts': set(),
        'severity': 'warning',
        'patterns': []
    })
    
    for key, data in error_counts.items():
        error_type = key.split(':')[0]
        grouped_errors[error_type]['count'] += data['count']
        grouped_errors[error_type]['contexts'].update(data['contexts'])
        grouped_errors[error_type]['severity'] = data['severity'] if data['severity'] == 'error' else grouped_errors[error_type]['severity']
        
        # Aggiungi esempi unici
        for example in data['examples']:
            if example not in grouped_errors[error_type]['examples']:
                if len(grouped_errors[error_type]['examples']) < 5:
                    grouped_errors[error_type]['examples'].append(example)
    
    # Crea lista di issue
    issues = []
    for error_type, data in sorted(grouped_errors.items(), key=lambda x: x[1]['count'], reverse=True):
        if data['count'] > 0:
            issues.append({
                'type': error_type,
                'severity': data['severity'],
                'count': data['count'],
                'contexts': sorted(list(data['contexts'])),
                'examples': data['examples']
            })
    
    # Calcola totali
    total_errors = sum(1 for issue in issues if issue['severity'] == 'error')
    total_warnings = sum(1 for issue in issues if issue['severity'] == 'warning')
    
    # Determina status
    if total_errors > 0:
        status = 'error'
        message = f'Trovati {total_errors} problema/i critico/i e {total_warnings} warning'
    elif total_warnings > 0:
        status = 'warning'
        message = f'Trovati {total_warnings} warning'
    else:
        status = 'success'
        message = 'Nessun problema rilevato'
    
    return {
        'status': status,
        'message': message,
        'file': file_path,
        'total_errors': total_errors,
        'total_warnings': total_warnings,
        'issues': issues
    }

def print_results(result):
    """Stampa i risultati in modo formattato"""
    print("=" * 80)
    print("RISULTATI ANALISI")
    print("=" * 80)
    print()
    
    # Status
    status_labels = {
        'error': '[ERRORE]',
        'warning': '[WARNING]',
        'success': '[OK]'
    }
    label = status_labels.get(result['status'], '[INFO]')
    print(f"{label} STATO: {result['status'].upper()}")
    print(f"[*] Messaggio: {result['message']}")
    print(f"[*] File: {result['file']}")
    print(f"[!] Errori: {result['total_errors']}")
    print(f"[!] Warning: {result['total_warnings']}")
    print()
    
    if result['issues']:
        print("-" * 80)
        print("PROBLEMI TROVATI:")
        print("-" * 80)
        print()
        
        for i, issue in enumerate(result['issues'], 1):
            severity_label = '[ERRORE]' if issue['severity'] == 'error' else '[WARNING]'
            print(f"{severity_label} PROBLEMA #{i}: {issue['type']}")
            print(f"   Severita: {issue['severity'].upper()}")
            print(f"   Occorrenze: {issue['count']}")
            
            if issue['contexts']:
                print(f"   Contesti: {', '.join(issue['contexts'])}")
            
            if issue['examples']:
                print(f"   Esempi ({len(issue['examples'])}):")
                for j, example in enumerate(issue['examples'], 1):
                    # Tronca a 300 caratteri ma mostra informazioni importanti
                    if len(example) > 300:
                        # Cerca di trovare un punto di taglio sensato
                        truncated = example[:297] + "..."
                        # Se contiene il percorso del file, mantienilo
                        if 'OptimizationHelper.php' in example:
                            # Estrai il percorso e la riga
                            match = re.search(r'([^:]+\.php):(\d+)', example)
                            if match:
                                file_info = f"{match.group(1)}:{match.group(2)}"
                                print(f"      {j}. {file_info} - {truncated[:200]}...")
                            else:
                                print(f"      {j}. {truncated}")
                        else:
                            print(f"      {j}. {truncated}")
                    else:
                        print(f"      {j}. {example}")
            
            print()
    else:
        print("[+] Nessun problema trovato!")
    
    print("=" * 80)

if __name__ == '__main__':
    # Percorso del file di log
    log_file = r"C:\Users\g.salvi\Dropbox\Siti internet\Altri siti\Principali\TotalDesign.it\wp-content\plugins\gik25-microdata\logs\apache_wordpress-1340912-5319280.cloudwaysapps.com.error.log"
    
    # Verifica che il file esista
    if not os.path.exists(log_file):
        print(f"[-] Errore: File non trovato: {log_file}")
        exit(1)
    
    # Analizza
    result = analyze_log_file(log_file, max_lines=20000)
    
    # Stampa risultati
    print_results(result)

