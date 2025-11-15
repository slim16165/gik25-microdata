const { TextControl } = wp.components;
const { SelectControl } = wp.components;
const { dispatch, select } = wp.data;
const { PluginDocumentSettingPanel } = wp.editor;
const { Component } = wp.element;
const { __ } = wp.i18n;

export default class Sidebar extends Component {
    constructor(props) {
        super(...arguments);

        // The state is used only to rerender the component with setState
        this.state = {
            seoPower: '',
            enableAil: 'text',
        };
    }

    componentDidMount() {
        const meta = select('core/editor').getEditedPostAttribute('meta');
        let seoPower = meta['_daim_seo_power'];
        let enableAil = meta['_daim_enable_ail'];

        if (seoPower === '' || seoPower === undefined) {
            seoPower = window.DAIM_PARAMETERS.default_seo_power;
        }

        if (enableAil === '' || enableAil === undefined) {
            enableAil = window.DAIM_PARAMETERS.enable_ail;
        }

        this.setState({
            seoPower: seoPower,
            enableAil: enableAil,
        });
    }

    render() {
        // Do not render anything if the user does not have the required capability.
        if (parseInt(window.DAIM_PARAMETERS.user_has_interlinks_options_mb_required_capability, 10) !== 1) {
            return null;
        }

        // Do not render anything if this editor tool is not enabled in this post type.
        if (parseInt(window.DAIM_PARAMETERS.interlinks_options_is_active_in_post_type, 10) !== 1) {
            return null;
        }

        return (
            <PluginDocumentSettingPanel
                name="interlinks-manager"
                title={__('Interlinks Options', 'interlinks-manager')}
            >
                <TextControl
                    label={__('SEO Power', 'interlinks-manager')}
                    type={'number'}
                    help={__('Control the amount of link juice assigned to the internal links of this post.', 'interlinks-manager')}
                    value={this.state.seoPower}
                    onChange={(value) => {
                        dispatch('core/editor').editPost({
                            meta: {
                                '_daim_seo_power': value,
                            },
                        });

                        // Used to rerender the component
                        this.setState({
                            seoPower: value,
                        });
                    }}
                    __nextHasNoMarginBottom={true}
                    __next40pxDefaultSize={true}
                    className="daim-interlinks-options-custom-margin"
                />

                <SelectControl
                    label={__('Enable AIL', 'interlinks-manager')}
                    help={__('Enable or disable auto internal links for this post.', 'interlinks-manager')}
                    value={this.state.enableAil}
                    options={[
                        { label: __('No', 'interlinks-manager'), value: '0' },
                        { label: __('Yes', 'interlinks-manager'), value: '1' },
                    ]}
                    onChange={(value) => {
                        dispatch('core/editor').editPost({
                            meta: {
                                '_daim_enable_ail': value,
                            },
                        });

                        // Used to rerender the component
                        this.setState({
                            enableAil: value,
                        });
                    }}
                    __nextHasNoMarginBottom={true}
                    __next40pxDefaultSize={true}
                />
            </PluginDocumentSettingPanel>
        );
    }
}