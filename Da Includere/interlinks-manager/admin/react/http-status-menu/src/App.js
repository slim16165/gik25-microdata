import Table from './components/Table';
import {downloadFileFromString} from "../../utils/utils";
import RefreshIcon from '../../../assets/img/icons/refresh-cw-01.svg';
import LoadingScreen from "../../shared-components/LoadingScreen";

const useState = wp.element.useState;
const useEffect = wp.element.useEffect;

const {__} = wp.i18n;

const App = () => {

    const [formData, setFormData] = useState(
        {
            statusCode: 'all',
            searchString: '',
            searchStringChanged: false,
            sortingColumn: 'last_check_date',
            sortingOrder: 'desc'
        }
    );

    const [dataAreLoading, setDataAreLoading] = useState(true);

    const [dataUpdateRequired, setDataUpdateRequired] = useState(false);

    const [tableData, setTableData] = useState([]);
    const [statistics, setStatistics] = useState({
        allPosts: 0,
        SuccessfulResponses: 0
    });

    useEffect(() => {

        setDataAreLoading(true);

        /**
         * Initialize the chart data with the data received from the REST API
         * endpoint provided by the plugin.
         */
        wp.apiFetch({
            path: '/interlinks-manager-pro/v1/http-status',
            method: 'POST',
            data: {
                status_code: formData.statusCode,
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
                    allPosts: data.statistics.all_posts,
                    successfullResponses: data.statistics.successful_responses
                });

                if (dataUpdateRequired) {

                    // Set the dataUpdateRequired state to false.
                    setDataUpdateRequired(false);

                    // Set the form data to the initial state.
                    setFormData({
                        statusCode: 'all',
                        searchString: '',
                        searchStringChanged: false,
                        sortingColumn: 'last_check_date',
                        sortingOrder: 'desc'
                    });

                }

                setDataAreLoading(false);

            },
        );

    }, [
        formData.statusCode,
        formData.searchStringChanged,
        formData.sortingColumn,
        formData.sortingOrder,
        dataUpdateRequired
    ]);

    /**
     * Function to handle key press events.
     *
     * @param event
     */
    function handleKeyUp(event) {

        // Check if Enter key is pressed (key code 13)
        if (event.key === 'Enter') {
            event.preventDefault(); // Prevent form submission.
            document.getElementById('daim-search-button').click(); // Simulate click on search button.
        }

    }

    /**
     * Used by the Navigation component.
     *
     * @param e
     */
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

    /**
     * Used to toggle the dataUpdateRequired value.
     *
     * @param e
     */
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
            path: '/interlinks-manager-pro/v1/http-status-menu-export-csv',
            method: 'POST'
        }).then(response => {

                downloadFileFromString(response.csv_content, 'http-status');

            },
        );

    }

    return (

        <>

            <React.StrictMode>

                {
                    !dataAreLoading ?

                        <div className={'daim-react-table'}>

                                <div className={'daim-react-table-header'}>
                                    <div className={'statistics'}>
                                        <div className={'statistic-label'}>{__('All posts', 'interlinks-manager')}:</div>
                                        <div className={'statistic-value'}>{statistics.allPosts}</div>
                                        <div className={'statistic-label'}>Successful reponses:</div>
                                        <div className={'statistic-value'}>{statistics.successfullResponses}</div>
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

                                <div className={'daim-react-table__daim-filters'}>

                                    <div className={'daim-pills'}>
                                        <button className={'daim-pill'}
                                                data-checked={formData.statusCode === 'all' ? 'true' : 'false'}
                                                onClick={() => setFormData({...formData, statusCode: 'all'})}
                                        >{__('All', 'interlinks-manager')}
                                        </button>
                                        <button className={'daim-pill'}
                                                data-checked={formData.statusCode === 'unknown' ? 'true' : 'false'}
                                                onClick={() => setFormData({...formData, statusCode: 'unknown'})}
                                        >{__('Unknown', 'interlinks-manager')}
                                        </button>
                                        <button className={'daim-pill'}
                                                data-checked={formData.statusCode === '1xx' ? 'true' : 'false'}
                                                onClick={() => setFormData({...formData, statusCode: '1xx'})}
                                        >1xx
                                        </button>
                                        <button className={'daim-pill'}
                                                data-checked={formData.statusCode === '2xx' ? 'true' : 'false'}
                                                onClick={() => setFormData({...formData, statusCode: '2xx'})}
                                        >2xx
                                        </button>
                                        <button className={'daim-pill'}
                                                data-checked={formData.statusCode === '3xx' ? 'true' : 'false'}
                                                onClick={() => setFormData({...formData, statusCode: '3xx'})}
                                        >3xx
                                        </button>
                                        <button className={'daim-pill'}
                                                data-checked={formData.statusCode === '4xx' ? 'true' : 'false'}
                                                onClick={() => setFormData({...formData, statusCode: '4xx'})}
                                        >4xx
                                        </button>
                                        <button className={'daim-pill'}
                                                data-checked={formData.statusCode === '5xx' ? 'true' : 'false'}
                                                onClick={() => setFormData({...formData, statusCode: '5xx'})}
                                        >5xx
                                        </button>
                                    </div>
                                    <div className={'daim-search-container'}>
                                        <input onKeyUp={handleKeyUp} type={'text'}
                                               placeholder={__('Filter by title', 'interlinks-manager')}
                                               value={formData.searchString}
                                               onChange={(event) => setFormData({
                                                   ...formData,
                                                   searchString: event.target.value
                                               })}
                                        />
                                        <input id={'daim-search-button'} className={'daim-btn daim-btn-secondary'}
                                               type={'submit'} value={__('Search', 'interlinks-manager')}
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
                                />

                            </div>

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