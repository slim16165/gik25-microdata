import Table from './components/Table';
import {downloadFileFromString} from '../../utils/utils';
import RefreshIcon from '../../../assets/img/icons/refresh-cw-01.svg';
import LoadingScreen from "../../shared-components/LoadingScreen";

const useState = wp.element.useState;
const useEffect = wp.element.useEffect;

const {__} = wp.i18n;

const App = () => {

    const [formData, setFormData] = useState(
        {
            optimizationStatus: 0,
            searchString: '',
            searchStringChanged: false,
            sortingColumn: 'post_date',
            sortingOrder: 'desc'
        }
    );

    const [dataAreLoading, setDataAreLoading] = useState(true);

    const [dataUpdateRequired, setDataUpdateRequired] = useState(false);

    const [tableData, setTableData] = useState([]);
    const [statistics, setStatistics] = useState({
        allPosts: 0,
        averageMil: 0,
        averageAil: 0
    });

    useEffect(() => {

        setDataAreLoading(true);

        /**
         * Initialize the chart data with the data received from the REST API
         * endpoint provided by the plugin.
         */
        wp.apiFetch({
            path: '/interlinks-manager-pro/v1/statistics',
            method: 'POST',
            data: {
                optimization_status: formData.optimizationStatus,
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
                    averageMil: data.statistics.average_mil,
                    averageAil: data.statistics.average_ail
                });

                if (dataUpdateRequired) {

                    // Set the dataUpdateRequired state to false.
                    setDataUpdateRequired(false);

                    // Set the form data to the initial state.
                    setFormData({
                        optimizationStatus: 0,
                        searchString: '',
                        searchStringChanged: false,
                        sortingColumn: 'post_date',
                        sortingOrder: 'desc'
                    });

                }

                setDataAreLoading(false);

            },
        );

    }, [
        formData.optimizationStatus,
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

        // Check if Enter key is pressed (key code 13).
        if (event.key === 'Enter') {
            event.preventDefault(); // Prevent form submission.
            document.getElementById('daim-search-button').click(); // Simulate click on search button.
        }

    }

    /**
     * Handle sorting changes.
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
            path: '/interlinks-manager-pro/v1/dashboard-menu-export-csv',
            method: 'POST'
        }).then(response => {

                downloadFileFromString(response.csv_content, 'dashboard');

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
                                    <div className={'statistic-label'}>{__('Average MIL', 'interlinks-manager')}:</div>
                                    <div className={'statistic-value'}>{statistics.averageMil}</div>
                                    <div className={'statistic-label'}>{__('Average AIL', 'interlinks-manager')}:</div>
                                    <div className={'statistic-value'}>{statistics.averageAil}</div></div>
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
                                            data-checked={formData.optimizationStatus === 0 ? 'true' : 'false'}
                                            onClick={() => setFormData({...formData, optimizationStatus: 0})}
                                    >All
                                    </button>
                                    <button className={'daim-pill'}
                                            data-checked={formData.optimizationStatus === 1 ? 'true' : 'false'}
                                            onClick={() => setFormData({...formData, optimizationStatus: 1})}
                                    >Not Optimized
                                    </button>
                                    <button className={'daim-pill'}
                                            data-checked={formData.optimizationStatus === 2 ? 'true' : 'false'}
                                            onClick={() => setFormData({...formData, optimizationStatus: 2})}
                                    >Optimized
                                    </button>
                                </div>
                                <div className={'daim-search-container'}>
                                    <input
                                        onKeyUp={handleKeyUp}
                                        type={'text'} placeholder={__('Filter by title', 'interlinks-manager')}
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