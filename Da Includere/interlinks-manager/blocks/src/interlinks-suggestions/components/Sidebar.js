const { Button } = wp.components;
const { dispatch, select } = wp.data;
const { PluginDocumentSettingPanel } = wp.editor;
const { Component, createRef } = wp.element;
const { __ } = wp.i18n;

export default class Sidebar extends Component {
  constructor(props) {
    super(...arguments);

    this.state = {
      suggestions: [], // Store suggestions
    };

    // Create a ref for the hidden input field
    this.hiddenInputRef = createRef();
  }

  handleCopyButtonClick = (index) => {
    // Find the link associated with the copy button
    const linkElement = document.querySelector(`.daim-suggestion-link[data-index="${index}"]`);
    if (linkElement) {
      const hrefValue = linkElement.getAttribute('href');

      // Set the href value in the hidden input field
      if (this.hiddenInputRef.current) {
        this.hiddenInputRef.current.value = hrefValue;

        // Ensure the input exists before selecting and copying
        if (this.hiddenInputRef.current) {
          this.hiddenInputRef.current.select();
          document.execCommand('copy');

          const noticeId = 'daim-link-copied-notice'; // Unique ID for the notice

          wp.data.dispatch('core/notices').createNotice(
              'success',                         // Notice type: success | info | warning | error
              'Copied link to clipboard.',      // Message
              {
                id: noticeId,
                isDismissible: true,
                type: 'snackbar',              // Makes it appear temporarily like a toast
              }
          );

          setTimeout(() => {
            wp.data.dispatch('core/notices').removeNotice(noticeId);
          }, 2000); // Remove after 2 seconds.

        }
      }
    }
  };

  renderSuggestions() {

    if (!Array.isArray(this.state.suggestions) || this.state.suggestions.length === 0) {
      return (
          <p>
            {__('There are no interlink suggestions available. Please ensure you have at least five other posts that meet the criteria defined in the "Suggestions" options.', 'interlinks-manager')}
          </p>
      );
    }

    return this.state.suggestions.map((item, index) => (
      <div className="daim-interlinks-suggestions-item" key={index}>
        <div className="daim-interlinks-suggestions-container-left">
          <a
              href={item.link}
              target="_blank"
              rel="noopener noreferrer"
              className="daim-suggestion-link"
              data-index={index + 1}
          >
            {item.icon_svg && (
                <span
                    className="daim-suggestion-icon"
                    dangerouslySetInnerHTML={{__html: item.icon_svg}}
                />
            )}
            <span className="daim-suggestion-title">{item.title}</span>
            <span className="components-external-link__icon" aria-label="(opens in a new tab)">↗</span>
          </a>
          <span className="daim-interlinks-suggestions-post-type">
            {item.post_type}
          </span>
        </div>
        <div
          className="daim-interlinks-suggestions-copy-button"
          data-index={index + 1}
          onClick={() => this.handleCopyButtonClick(index + 1)}
          aria-label={'Copy'}
        >
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path fillRule="evenodd" clipRule="evenodd" d="M5.625 5.5h9.75c.069 0 .125.056.125.125v9.75a.125.125 0 0 1-.125.125h-9.75a.125.125 0 0 1-.125-.125v-9.75c0-.069.056-.125.125-.125ZM4 5.625C4 4.728 4.728 4 5.625 4h9.75C16.273 4 17 4.728 17 5.625v9.75c0 .898-.727 1.625-1.625 1.625h-9.75A1.625 1.625 0 0 1 4 15.375v-9.75Zm14.5 11.656v-9H20v9C20 18.8 18.77 20 17.251 20H6.25v-1.5h11.001c.69 0 1.249-.528 1.249-1.219Z"></path></svg>
        </div>
      </div>
    ));
  }

  render() {

    // Do not render anything if the user does not have the required capability.
    if (parseInt(window.DAIM_PARAMETERS.user_has_interlinks_suggestions_mb_required_capability, 10) !== 1) {
      return null;
    }

    // Do not render anything if this editor tool is not enabled in this post type.
    if (parseInt(window.DAIM_PARAMETERS.interlinks_suggestions_is_active_in_post_type, 10) !== 1) {
      return null;
    }

    return (
      <PluginDocumentSettingPanel
        name="interlinks-manager"
        title={__('Interlinks Suggestions', 'interlinks-manager')}
      >
        <div className="daim-container">
          <input
            type="text"
            ref={this.hiddenInputRef}
            style={{ position: 'absolute', left: '-9999px' }} // Hidden input field
            readOnly
          />
          <div id="daim-meta-message">
            {this.state.suggestions.length > 0 || this.state.suggestions.message ? (
              this.renderSuggestions()
            ) : (
              <p>
                {__(
                  'Click the "Generate" button multiple times until you find posts suitable to be used as internal links.',
                  'interlinks-manager'
                )}
              </p>
            )}
          </div>
          <div className="daim-buttons-container">
            <Button
              variant="secondary"
              className="editor-post-trash daim-generate-file-button"
              onClick={() => {
                const postId = parseInt(document.getElementById('post_ID').value, 10);

                wp.apiFetch({
                  path: '/interlinks-manager-pro/v1/generate-interlinks-suggestions',
                  method: 'POST',
                  data: { id: postId },
                }).then((response) => {
                  if (!response.error) {
                    this.setState({ suggestions: response });
                  }
                });
              }}
            >
              {__('Generate', 'interlinks-manager')}
            </Button>
          </div>
        </div>
      </PluginDocumentSettingPanel>
    );
  }
}