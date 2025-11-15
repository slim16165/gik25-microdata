<?php
/**
 * Export/Import - Handles CSV and Excel export/import
 *
 * @package gik25microdata\InternalLinks\Integration
 */

namespace gik25microdata\InternalLinks\Integration;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Export/Import class
 */
class ExportImport
{
    /**
     * Export links to CSV
     *
     * @param array $filters Filters
     * @return void
     */
    public function exportCSV($filters = [])
    {
        $manager = \gik25microdata\InternalLinks\Core\InternalLinksManager::getInstance();
        $links = $manager->generateReport('links', $filters);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="internal-links-' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Source Post', 'Target URL', 'Anchor Text', 'Type', 'Clicks']);

        foreach ($links as $link) {
            fputcsv($output, [
                $link['source_title'] ?? '',
                $link['target_url'] ?? '',
                $link['anchor_text'] ?? '',
                $link['link_type'] ?? '',
                $link['click_count'] ?? 0,
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Export links to Excel
     *
     * @param array $filters Filters
     * @return void
     */
    public function exportExcel($filters = [])
    {
        if (!class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            wp_die('PhpSpreadsheet library not available');
        }

        $manager = \gik25microdata\InternalLinks\Core\InternalLinksManager::getInstance();
        $links = $manager->generateReport('links', $filters);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $sheet->setCellValue('A1', 'Source Post');
        $sheet->setCellValue('B1', 'Target URL');
        $sheet->setCellValue('C1', 'Anchor Text');
        $sheet->setCellValue('D1', 'Type');
        $sheet->setCellValue('E1', 'Clicks');

        // Data
        $row = 2;
        foreach ($links as $link) {
            $sheet->setCellValue('A' . $row, $link['source_title'] ?? '');
            $sheet->setCellValue('B' . $row, $link['target_url'] ?? '');
            $sheet->setCellValue('C' . $row, $link['anchor_text'] ?? '');
            $sheet->setCellValue('D' . $row, $link['link_type'] ?? '');
            $sheet->setCellValue('E' . $row, $link['click_count'] ?? 0);
            $row++;
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="internal-links-' . date('Y-m-d') . '.xlsx"');
        $writer->save('php://output');
        exit;
    }

    /**
     * Import keywords from CSV
     *
     * @param string $file_path CSV file path
     * @return array Import results
     */
    public function importKeywords($file_path)
    {
        // TODO: Implement CSV import
        return [
            'success' => false,
            'message' => 'Not implemented yet',
        ];
    }
}

