const { Button } = wp.components;
const { PluginDocumentSettingPanel } = wp.editor;
const { useState, useEffect } = wp.element;
const { __ } = wp.i18n;
const apiFetch = wp.apiFetch;

const Sidebar = () => {

    // Do not render anything if the user does not have the required capability.
    if (parseInt(window.DAIM_PARAMETERS.user_has_interlinks_optimization_mb_required_capability, 10) !== 1) {
        return null;
    }

    // Do not render anything if this editor tool is not enabled in this post type.
    if (parseInt(window.DAIM_PARAMETERS.interlinks_optimization_is_active_in_post_type, 10) !== 1) {
        return null;
    }

  const [optimizationData, setOptimizationData] = useState(null);

    // Fetch interlinks optimization data when the component mounts and on post save.
    useEffect(() => {
        const postId = parseInt(document.getElementById('post_ID').value, 10);

        const fetchData = () => {
            wp.apiFetch({
                path: '/interlinks-manager-pro/v1/generate-interlinks-optimization',
                method: 'POST',
                data: { id: postId },
            })
                .then((response) => {
                    setOptimizationData(response);
                })
                .catch((error) => {
                    console.error('Error fetching interlinks optimization data:', error);
                });
        };

        // Initial fetch
        fetchData();

        let wasSaving = false;

        const unsubscribe = wp.data.subscribe(() => {
            const isSaving = wp.data.select('core/editor').isSavingPost();
            const isAutosaving = wp.data.select('core/editor').isAutosavingPost();

            // Detect when a manual save completes
            if (wasSaving && !isSaving && !isAutosaving) {
                fetchData(); // Re-fetch optimization data
            }

            wasSaving = isSaving;
        });

        return () => {
            unsubscribe();
        };
    }, []);

  return (
      <PluginDocumentSettingPanel
          name="interlinks-manager"
          title={__('Interlinks Optimization', 'interlinks-manager')}
      >
        <div className="daim-container">
          <div className="daim-meta-message">
            {optimizationData ? (() => {
              const totalNumberOfInterlinks = optimizationData['total_number_of_interlinks'];
              const numberOfManualInterlinks = optimizationData['number_of_manual_interlinks'];
              const numberOfAutoInterlinks = optimizationData['number_of_autolinks'];
              const suggestedMin = optimizationData['suggested_min_number_of_interlinks'];
              const suggestedMax = optimizationData['suggested_max_number_of_interlinks'];

              return totalNumberOfInterlinks >= suggestedMin && totalNumberOfInterlinks <= suggestedMax ? (
                  <p>{__('The number of internal links in this post is optimized.', 'interlinks-manager')}</p>
              ) : (
                  <>
                    <p>
                      {__('Please optimize the number of internal links. This post currently contains', 'interlinks-manager')}&nbsp;
                      {totalNumberOfInterlinks}&nbsp;
                      {totalNumberOfInterlinks === 1
                          ? __('internal link', 'interlinks-manager')
                          : __('internal links', 'interlinks-manager')}
                      . ({numberOfManualInterlinks}&nbsp;
                      {numberOfManualInterlinks === 1
                          ? __('manual internal link', 'interlinks-manager')
                          : __('manual internal links', 'interlinks-manager')}
                      &nbsp;
                      {__('and', 'interlinks-manager')}&nbsp;
                      {numberOfAutoInterlinks}&nbsp;
                      {numberOfAutoInterlinks === 1
                          ? __('auto internal link', 'interlinks-manager')
                          : __('auto internal links', 'interlinks-manager')}
                      )
                    </p>

                    {suggestedMin === suggestedMax ? (
                        <p>
                          {__('Based on the content length and your settings, the ideal number of internal links should be', 'interlinks-manager')}&nbsp;
                          {suggestedMin}.
                        </p>
                    ) : (
                        <p>
                          {__('Based on the content length and your settings, the ideal number of internal links should fall between', 'interlinks-manager')}&nbsp;
                          {suggestedMin}&nbsp;
                          {__('and', 'interlinks-manager')}&nbsp;
                          {suggestedMax}.
                        </p>
                    )}
                  </>
              );
            })() : (
                <p></p>
            )}
          </div>
        </div>
      </PluginDocumentSettingPanel>
  );
};

export default Sidebar;
