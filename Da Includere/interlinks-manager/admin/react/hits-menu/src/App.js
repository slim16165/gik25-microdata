import Table from './components/Table';
import {downloadFileFromString} from '../../utils/utils';
import LoadingScreen from "../../shared-components/LoadingScreen";

const useState = wp.element.useState;
const useEffect = wp.element.useEffect;

const {__} = wp.i18n;

const App = () => {

    const [formData, setFormData] = useState(
        {
            searchString: '',
            searchStringChanged: false,
            sortingColumn: 'id',
            sortingOrder: 'desc'
        }
    );

    const [dataAreLoading, setDataAreLoading] = useState(true);

    const [tableData, setTableData] = useState([]);
    const [statistics, setStatistics] = useState({
        allClicks: 0,
        autolinksPercentage: 0
    });

    useEffect(() => {

        setDataAreLoading(true);

        /**
         * Initialize the chart data with the data received from the REST API
         * endpoint provided by the plugin.
         */
        wp.apiFetch({
            path: '/interlinks-manager-pro/v1/hits',
            method: 'POST',
            data: {
                search_string: formData.searchString,
                sorting_column: formData.sortingColumn,
                sorting_order: formData.sortingOrder
                // nonce: window.DAEXTREVOP_PARAMETERS.read_requests_nonce
            }
        }).then(data => {

                // Set the table data with setTableData().
                setTableData(data.table);

                // Set the statistics.
                setStatistics({
                    allClicks: data.statistics.all_clicks,
                    autolinksPercentage: data.statistics.autolinks_percentage
                });

                setDataAreLoading(false);

            },
        );

    }, [
        formData.searchStringChanged,
        formData.sortingColumn,
        formData.sortingOrder
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

    //Used by the Navigation component
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
     * Download the file with the CSV data.
     */
    function downloadExportFile() {

        wp.apiFetch({
            path: '/interlinks-manager-pro/v1/hits-menu-export-csv',
            method: 'POST'
        }).then(response => {

                downloadFileFromString(response.csv_content, 'hits');

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
                                        <div className={'statistic-label'}>{__('All clicks', 'interlinks-manager')}:</div>
                                        <div className={'statistic-value'}>{statistics.allClicks}</div>
                                        <div className={'statistic-label'}>{__('Autolinks', 'interlinks-manager')} %:</div>
                                        <div className={'statistic-value'}>{statistics.autolinksPercentage}</div>
                                    </div>
                                    <div className={'tools-actions'}>
                                        <button onClick={() => {
                                            downloadExportFile()
                                        }}
                                                {...(tableData.length === 0 ? {disabled: 'disabled'} : {})}
                                        >
                                            {__('Export', 'interlinks-manager')}
                                        </button>
                                    </div>
                                </div>

                                <div className={'daim-react-table__daim-filters daim-react-table__daim-filters-hits-menu'}>

                                    <div className={'daim-search-container'}>
                                        <input onKeyUp={handleKeyUp} type={'text'}
                                               placeholder={__('Filter by title or target', 'interlinks-manager')}
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
                        />
                }

            </React.StrictMode>

        </>

    );

};
export default App;