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
