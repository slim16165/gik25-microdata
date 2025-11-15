import Table from './components/Table';
import TableUrlDetails from './components/TableUrlDetails';
import {downloadFileFromString} from '../../utils/utils';
import RefreshIcon from '../../../assets/img/icons/refresh-cw-01.svg';
import LoadingScreen from "../../shared-components/LoadingScreen";

const useState = wp.element.useState;
const useEffect = wp.element.useEffect;

const {__} = wp.i18n;

const App = () => {

    const [formData, setFormData] = useState(
        {
            urlDetailsView: false,
            urlDetailsViewId: 0,
            urlDetailsViewUrl: '',
            searchString: '',
            searchStringChanged: false,
            sortingColumn: 'juice',
            sortingOrder: 'desc'
        }
    );

    const [dataAreLoading, setDataAreLoading] = useState(true);

    const [dataUpdateRequired, setDataUpdateRequired] = useState(false);

    const [tableData, setTableData] = useState([]);
    const [statistics, setStatistics] = useState({
        allUrls: 0,
        averageIil: 0,
        averageJuice: 0
    });

    useEffect(() => {

        if (formData.urlDetailsView) {
            return;
        }

        setDataAreLoading(true);

        /**
         * Initialize the chart data with the data received from the REST API
         * endpoint provided by the plugin.
         */
        wp.apiFetch({
            path: '/interlinks-manager-pro/v1/juice',
            method: 'POST',
            data: {
                search_string: formData.searchString,
                sorting_column: formData.sortingColumn,
                sorting_order: formData.sortingOrder,
                data_update_required: dataUpdateRequired
            }
        }).then(data => {

                // Set the table data with setTableData().
                setTableData(data.table);

                // Set the statistics.
                setStatistics({
                    allPosts: data.statistics.all_urls,
                    averageMil: data.statistics.average_iil,
                    averageAil: data.statistics.average_juice
                });

                if (dataUpdateRequired) {

                    // Set the dataUpdateRequired state to false.
                    setDataUpdateRequired(false);

                    // Set the form data to the initial state.
                    setFormData({
                        urlDetailsView: false,
                        urlDetailsViewId: 0,
                        urlDetailsViewUrl: '',
                        searchString: '',
                        searchStringChanged: false,
                        sortingColumn: 'juice_relative',
                        sortingOrder: 'desc'
                    });

                }

                setDataAreLoading(false);

            },
        );

    }, [
        formData.searchStringChanged,
        formData.sortingColumn,
        formData.sortingOrder,
        formData.urlDetailsView,
        dataUpdateRequired
    ]);

    useEffect(() => {

        if (!formData.urlDetailsView) {
            return;
        }

        /**
         * Initialize the chart data with the data received from the REST API
         * endpoint provided by the plugin.
         */
        wp.apiFetch({
            path: '/interlinks-manager-pro/v1/juice-url',
            method: 'POST',
            data: {
                id: formData.urlDetailsViewId,
            }
        }).then(data => {

                // Set the table data with setTableData().
                setTableData(data);

            },
        );

    }, [
        formData.urlDetailsView
    ]);

    /**
     * Function to handle key press events.
     *
     * @param event
     */
    function handleKeyUp(event) {

        // Check if Enter key is pressed (key code 13).
        if (event.key === 'Enter') {
            event.preventDefault(); // Prevent form submission.
            document.getElementById('daim-search-button').click(); // Simulate click on search button.
        }

    }

    // Used by the Navigation component.
    function handleSortingChanges(e) {


        /**
         * Check if the sorting column is the same as the previous one.
         * If it is, change the sorting order.
         * If it is not, change the sorting column and set the sorting order to 'asc'.
         */
        let sortingOrder = formData.sortingOrder;
        if (formData.sortingColumn === e.target.value) {
            sortingOrder = formData.sortingOrder === 'asc' ? 'desc' : 'asc';
        }

        setFormData({
            ...formData,
            sortingColumn: e.target.value,
            sortingOrder: sortingOrder
        })

    }

    function urlDetailsViewHandler(id, url) {

        setFormData({
            ...formData,
            urlDetailsView: true,
            urlDetailsViewId: id,
            urlDetailsViewUrl: url
        });

    }

    // Used to toggle the dataUpdateRequired value.
    function handleDataUpdateRequired(e) {
        setDataUpdateRequired(prevDataUpdateRequired => {
            return !prevDataUpdateRequired;
        });
    }

    /**
     * Download the file with the CSV data.
     */
    function downloadExportFile() {

        wp.apiFetch({
            path: '/interlinks-manager-pro/v1/juice-menu-export-csv',
            method: 'POST'
        }).then(response => {

                downloadFileFromString(response.csv_content, 'juice');

            },
        );

    }

    return (

        <>

            <React.StrictMode>

                {
                    !dataAreLoading ?

                        <>

                            {!formData.urlDetailsView && (
                                <div className={'daim-react-table'}>

                                        <div className={'daim-react-table-header'}>
                                            <div className={'statistics'}>
                                                <div className={'statistic-label'}>{__('All URLs', 'interlinks-manager')}:</div>
                                                <div className={'statistic-value'}>{statistics.allPosts}</div> Average
                                                <div className={'statistic-label'}>{__('IIL', 'interlinks-manager')}:</div>
                                                <div className={'statistic-value'}>{statistics.averageMil}</div> Average
                                                <div className={'statistic-label'}>{__('Juice', 'interlinks-manager')}:</div>
                                                <div className={'statistic-value'}>{statistics.averageAil}</div>
                                            </div>
                                            <div className={'tools-actions'}>
                                                <button
                                                    onClick={(event) => handleDataUpdateRequired(event)}
                                                ><img src={RefreshIcon} className={'button-icon'}></img>
                                                    {__('Update metrics', 'interlinks-manager')}
                                                </button>
                                                <button onClick={() => {
                                                    downloadExportFile()
                                                }}
                                                        {...(tableData.length === 0 ? {disabled: 'disabled'} : {})}
                                                >
                                                    {__('Export', 'interlinks-manager')}
                                                </button>
                                            </div>
                                        </div>

                                        <div className={'daim-react-table__daim-filters daim-react-table__daim-filters-juice-menu'}>

                                            <div className={'daim-search-container'}>
                                                <input onKeyUp={handleKeyUp} type={'text'}
                                                       placeholder={__('Filter by URL', 'interlinks-manager')}
                                                       value={formData.searchString}
                                                       onChange={(event) => setFormData({
                                                           ...formData,
                                                           searchString: event.target.value
                                                       })}
                                                />
                                                <input id={'daim-search-button'}
                                                       className={'daim-btn daim-btn-secondary'} type={'submit'}
                                                       value={__('Search', 'interlinks-manager')}
                                                       onClick={() => setFormData({
                                                           ...formData,
                                                           searchStringChanged: formData.searchStringChanged ? false : true
                                                       })}
                                                />
                                            </div>

                                        </div>

                                        <Table
                                            data={tableData}
                                            handleSortingChanges={handleSortingChanges}
                                            formData={formData}
                                            urlDetailsViewHandler={urlDetailsViewHandler}
                                        />

                                    </div>
                            )}

                            {formData.urlDetailsView && (
                                <div className={'daim-react-table url-details-view'}>


                                                <div className={'daim-react-table-header'}>
                                                    <div>{__('Internal Inbound Links for', 'interlinks-manager') + ' ' + formData.urlDetailsViewUrl}</div>
                                                    <a
                                                        className={'daim-back-button'}
                                                        onClick={() => setFormData({
                                                            ...formData,
                                                            urlDetailsView: false,
                                                            urlDetailsViewId: 0
                                                        })}
                                                    >{String.fromCharCode(8592)} {__('Back', 'interlinks-manager')}</a>
                                                </div>

                                                <TableUrlDetails
                                                    data={tableData}
                                                    handleSortingChanges={handleSortingChanges}
                                                    formData={formData}
                                                    urlDetailsViewHandler={urlDetailsViewHandler}
                                                />

                                            </div>
                            )}

                        </>

                        :

                        <LoadingScreen
                            loadingDataMessage={__('Loading data...', 'interlinks-manager')}
                            generatingDataMessage={__('Data is being generated. For large sites, this process may take several minutes. Please wait...', 'interlinks-manager')}
                            dataUpdateRequired={dataUpdateRequired}/>
                }

            </React.StrictMode>

        </>

    );

};
export default App;