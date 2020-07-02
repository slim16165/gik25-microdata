(function() {
    tinymce.create("tinymce.plugins.ReviousBoxinfo", {
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
            ed.addCommand("boxinfo", function() {
                var selected_text = ed.selection.getContent();
                var return_text = "";
                return_text = "[boxinformativo title=\"Curiosità\"]" + selected_text.replace(/<\/?p[^>]*>/g, " ") + "[/boxinformativo]<br/><br/>";
                ed.execCommand("mceInsertContent", 0, return_text);


            });


            // Pullquote Menu Button http://www.tinymce.com/wiki.php/api4:class.tinymce.ui.MenuButton
            ed.addButton("boxinfo-menu", {
                border : "1 1 1 1",
                text : "Boxinfo",
                tooltip : "Aggiunge il box azzurro rettangolare coi bordi sopra e sotto",
                icon: true,
                image : url + "/quote-left.png",
                size : "small",
                onclick: function() {
                    // ed.execCommand("boxinfo");

                    ed.windowManager.open( {
                        title: 'Box Info',
                        body: [{
                            type: 'listbox',
                            name: 'style',
                            label: 'Style',
                            'values': [
                                {text: 'Add Boxinfo', value: 'clear'},
                                // {text: 'White', value: 'white'},
                                // {text: 'Colour 1', value: 'colour1'},
                                // {text: 'Colour 2', value: 'colour2'},
                                // {text: 'Colour 3', value: 'colour3'},
                            ]
                        } /*add in the following with the comma */ ,
                            {   type: 'container',
                                html: '<p>Wrap the content in Box Info</p>'
                            }
                        ],
                        onsubmit: function( e ) {
                            ed.insertContent( '[boxinformativo title=\"Curiosità\"]' + ed.selection.getContent().replace(/<\/?p[^>]*>/g, " ") + '[/boxinformativo]<br/><br/>');
                        }});
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
                longname : "BoxInfo",
                author : "Revious",
                authorurl : "",
                infourl : "",
                version : "1.7.5"
            };
        }
    });

    // Register plugin
    tinymce.PluginManager.add("Revious_boxinfo", tinymce.plugins.ReviousBoxinfo);
})();