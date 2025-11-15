const {__} = wp.i18n;

const settings = {
    pluginName: 'Interlinks Manager',
    footerLinks: [
        {
            linkName: __('Interlinks Manager version 1.35', 'interlinks-manager'),
            linkUrl: 'https://daext.com/interlinks-manager/',
        },
        {
            linkName: __('Knowledge Base', 'interlinks-manager'),
            linkUrl: 'https://daext.com/kb-category/interlinks-manager/',
        },
        {
            linkName: __('Support', 'interlinks-manager'),
            linkUrl: 'https://daext.com/support/',
        },
        {
            linkName: __('Change Log', 'interlinks-manager'),
            linkUrl: 'https://codecanyon.net/item/interlinks-manager/13486900#item-description__updates',
        },
        {
            linkName: __('Premium Plugins', 'interlinks-manager'),
            linkUrl: 'https://daext.com/products/',
        }
    ],
    pages: window.DAEXTDAIM_PARAMETERS.options_configuration_pages,
};

export default settings;