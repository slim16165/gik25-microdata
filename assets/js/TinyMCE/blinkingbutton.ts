declare var tinymce: any;

tinymce.create("tinymce.plugins.md_blinkingbutton", {
    init: function (ed, url) {
        ed.addCommand("md_blinkingbutton", () => {
            const selected_text = ed.selection.getContent();
            let return_text = "";
            return_text =
                '[md_blinkingbutton fa_icon="fa fa-bars" url="" text="Sample Blinking Button"]' +
                selected_text.replace(/<\/?p[^>]*>/g, " ") +
                "[/md_blinkingbutton]<br/><br/>";
            ed.execCommand("mceInsertContent", 0, return_text);
        });

        ed.addButton("md_blinkingbutton-menu", {
            border: "1 1 1 1",
            text: "Blinking Button",
            tooltip: "Insert blinking button shortcode",
            icon: " fa fa-info-circle",
            image: url + "./../images/icon-blinking-button.png",
            size: "small",
            onclick: () => {
                ed.execCommand("md_blinkingbutton");
                return;
            },
        });
    },

    createControl: function (n, cm) {
        return null;
    },

    getInfo: function () {
        return {
            longname: "md_blinkingbutton",
            author: "Revious",
            authorurl: "",
            infourl: "",
            version: "1.7.5",
        };
    },
});

tinymce.PluginManager.add("md_blinkingbutton", tinymce.plugins.md_blinkingbutton);
