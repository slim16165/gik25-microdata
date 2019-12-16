/* ======================================
 * https://github.com/revious-microdata
 * version 1.7.5
 *
 * revious
 *
 * revious-microdata.js
 *
 * ======================================
*/
(function() {
    tinymce.create('tinymce.plugins.ReviousMicroData', {
        /**
         * Initializes the plugin, this will be executed after the plugin has been created.
         * This call is done before the editor instance has finished it's initialization so use the onInit event
         * of the editor instance to intercept that event.
         *
         * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
         * @param {string} url Absolute URL to where the plugin is located.
         */
        init : function(ed, url)
        {
            ed.addCommand('md_telefono', function() {
                var selected_text = ed.selection.getContent();
                var return_text = '';
                return_text = '[microdata_telefono organizationname=""]' + selected_text.replace(/<\/?p[^>]*>/g, " ") + '[/microdata_telefono]';
                ed.execCommand('mceInsertContent', 0, return_text);
            });


            // Pullquote Menu Button http://www.tinymce.com/wiki.php/api4:class.tinymce.ui.MenuButton
            ed.addButton('md_telefono_btn', {
                border : '1 1 1 1',
                text : 'Telefono',
                tooltip : 'Aggiunge i microdata di schema utili a Google',
                icon: true,
                image : url + '/quote-left.png',
                size : 'small',
                onclick: function() {ed.execCommand('md_telefono'); }
            });

            //-----------------------------------------

            ed.addCommand('md_prezzo', function() {
                var selected_text = ed.selection.getContent();
                var return_text = '';
                return_text = '[microdata_prezzo]' + selected_text.replace(/<\/?p[^>]*>/g, " ") + '[/microdata_prezzo]';
                ed.execCommand('mceInsertContent', 0, return_text);
            });


            // Pullquote Menu Button http://www.tinymce.com/wiki.php/api4:class.tinymce.ui.MenuButton
            ed.addButton('md_prezzo_btn', {
                border : '1 1 1 1',
                text : 'Prezzo',
                tooltip : 'Aggiunge i microdata di schema utili a Google',
                icon: true,
                image : url + '/quote-left.png',
                size : 'small',
                onclick: function() {ed.execCommand('md_prezzo'); }
            });
        },

        /**
         * Creates control instances based in the incomming name. This method is normally not
         * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
         * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
         * method can be used to create those.
         *
         * @param {String} n Name of the control to create.
         * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
         * @return {tinymce.ui.Control} New control instance or null if no control was created.
         */
        createControl : function(n, cm) {
            return null;
        },

        /**
         * Returns information about the plugin as a name/value array.
         * The current keys are longname, author, authorurl, infourl and version.
         *
         * @return {Object} Name/value array containing information about the plugin.
         */
        getInfo : function() {
            return {
                longname : 'MicroData',
                author : 'Revious',
                authorurl : '',
                infourl : '',
                version : '1.7.5'
            };
        }
    });

    // Register plugin
    tinymce.PluginManager.add('revious_microdata', tinymce.plugins.ReviousMicroData);
})();

// item creation dynamically
let createArr = (n) => {
    let arr = []
    for (let i = 0; i < n; i++) {
        arr.push(
            {type: 'label', text: 'Domanda'},
            {type: 'textbox', name: `domanda${i+1}`, multiline : true, style: 'width: 100%'},
            {type: 'label', text: 'Risposta'},
            {type: 'textbox', name: `risposta${i+1}`, multiline : true, style: 'width: 100%'},
        )
    }
    return arr;
}

function encodeHTML(str){

    var replacement = {'“':'\"','”':'\"'};
    str = str.replace(/[“”]/g, m => replacement[m]);

    return str.replace(/[\u00A0-\u9999<>&](?!#)/gim, function(i) {
        return '&#' + i.charCodeAt(0) + ';';
    });
}

// onsubmit handled dynamically
let handleQuesData = (data, n) => {
    let quesdata = '[domande_e_risposte]<br/>';

    let domande_risposte = [];
    for (let i = 0; i < n; i++)
    {
        let domanda_i = encodeHTML(data[`domanda${i+1}`]);
        let risposta_i = encodeHTML(data[`risposta${i+1}`]);

        domanda_i = JSON.stringify(domanda_i);
        risposta_i = JSON.stringify(risposta_i);

        //toglie le virgolette ad inizio e fine
        domanda_i = domanda_i.replace(/"(.*)"/mg, "$1");
        risposta_i = risposta_i.replace(/"(.*)"/mg, "$1");



        if(domanda_i &&  !(domanda_i === ""))
        {
            domande_risposte.push(`{"domanda": "${domanda_i}", "risposta": "${risposta_i}"}\n`);
        }
    }
    quesdata+= domande_risposte.join(",<br/>");
    return quesdata +'<br/>[/domande_e_risposte]';
}

let numItem = 5;

(function() {
    tinymce.create("tinymce.plugins.QuestionAndAnswer", {
        /**
         * Initializes the plugin, this will be executed after the plugin has been created.
         * This call is done before the editor instance has finished it's initialization so use the onInit event
         * of the editor instance to intercept that event.
         *
         * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
         * @param {string} url Absolute URL to where the plugin is located.
         */
        init : function(ed, url) {
            // Left-Aligned Pullquote
            ed.addCommand("DomandeERisposte", function() {
                ed.windowManager.open( {
                    title: 'Domande e risposte',
                    body: [
                        {type: 'container',
                        layout: 'stack',
                        columns: 2,
                        minWidth: 500,
                        minHeight: 650,
                        items: createArr(numItem)
                        }
                    ],
                    onsubmit: function( e ) {
                        ed.insertContent(handleQuesData(e.data, numItem));
                    }});
            });


            // Pullquote Menu Button http://www.tinymce.com/wiki.php/api4:class.tinymce.ui.MenuButton
            ed.addButton("DomandeERisposte_btn", {
                border : "1 1 1 1",
                text : "Domande",
                tooltip : "Aggiunge lo schema Question & Answers",
                icon: true,
                image : url + "/quote-left.png",
                size : "small",
                onclick: function() {
                    ed.execCommand("DomandeERisposte");


                }
            });
        },

        /**
         * Creates control instances based in the incomming name. This method is normally not
         * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
         * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
         * method can be used to create those.
         *
         * @param {String} n Name of the control to create.
         * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
         * @return {tinymce.ui.Control} New control instance or null if no control was created.
         */
        createControl : function(n, cm) {
            return null;
        },

        /**
         * Returns information about the plugin as a name/value array.
         * The current keys are longname, author, authorurl, infourl and version.
         *
         * @return {Object} Name/value array containing information about the plugin.
         */
        getInfo : function() {
            return {
                longname : "QuestionAndAnswer",
                author : "Revious",
                authorurl : "",
                infourl : "",
                version : "1.0.0"
            };
        }
    });

    // Register plugin
    tinymce.PluginManager.add("QuestionAndAnswer", tinymce.plugins.QuestionAndAnswer);
})();













