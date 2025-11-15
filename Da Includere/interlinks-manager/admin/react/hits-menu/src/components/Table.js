const useState = wp.element.useState;
import Pagination from '../../../shared-components/pagination/Pagination';

const useMemo = wp.element.useMemo;
const {__} = wp.i18n;

let PageSize = window.DAIM_PARAMETERS.items_per_page;

const Chart = (props) => {

    //Pagination - START --------------------------------------------------------

    const [currentPage, setCurrentPage] = useState(1);

    const currentTableData = useMemo(() => {
        const firstPageIndex = (currentPage - 1) * PageSize;
        const lastPageIndex = firstPageIndex + PageSize;
        return props.data.slice(firstPageIndex, lastPageIndex);
    }, [currentPage, props.data]);

    //Pagination - END ----------------------------------------------------------

    function handleDataIcon(columnName) {

        return props.formData.sortingColumn === columnName ? props.formData.sortingOrder : '';

    }

    return (

        <div className="daim-data-table-container">

            <table className="daim-react-table__daim-data-table daim-react-table__daim-data-table-hits-menu">
                <thead>
                <tr>
                    <th>
                        <button
                            className={'daim-react-table__daim-sorting-button'}
                            onClick={props.handleSortingChanges}
                            value={'id'}
                            data-icon={handleDataIcon('id')}
                        >{__('Tracking ID', 'interlinks-manager')}</button>
                    </th>
                    <th>
                        <button
                            className={'daim-react-table__daim-sorting-button'}
                            onClick={props.handleSortingChanges}
                            value={'post_title'}
                            data-icon={handleDataIcon('post_title')}
                        >{__('Post', 'interlinks-manager')}</button>
                    </th>
                    <th>
                        <button
                            className={'daim-react-table__daim-sorting-button'}
                            onClick={props.handleSortingChanges}
                            value={'date'}
                            data-icon={handleDataIcon('date')}
                        >{__('Date', 'interlinks-manager')}</button>
                    </th>
                    <th>
                        <button
                            className={'daim-react-table__daim-sorting-button'}
                            onClick={props.handleSortingChanges}
                            value={'target_url'}
                            data-icon={handleDataIcon('target_url')}
                        >{__('Target', 'interlinks-manager')}</button>
                    </th>
                    <th>
                        <button
                            className={'daim-react-table__daim-sorting-button'}
                            onClick={props.handleSortingChanges}
                            value={'link_type'}
                            data-icon={handleDataIcon('link_type')}
                        >{__('Type', 'interlinks-manager')}</button>
                    </th>
                </tr>
                </thead>
                <tbody>

                {currentTableData.map((row) => (
                    <tr key={row.id}>
                        <td>{row.id}</td>
                        <td>
                            <div className={'daim-react-table__post-cell-container'}>
                                <a href={row.post_permalink}>
                                    {row.post_title}
                                </a>
                                <a href={row.post_permalink} target={'_blank'}
                                   className={'daim-react-table__icon-link'}></a>
                                <a href={row.post_edit_link} className={'daim-react-table__icon-link'}></a>
                            </div>
                        </td>
                        <td>{row.formatted_date}</td>
                        <td>
                            <div className={'daim-react-table__post-cell-container'}>
                                <a href={row.target_url}>
                                    {row.target_url}
                                </a>
                                <a href={row.target_url} target={'_blank'}
                                   className={'daim-react-table__icon-link'}></a>
                            </div>
                        </td>
                        <td>{parseInt(row.link_type, 10) === 0 ? __('AIL', 'interlinks-manager') : __('MIL', 'interlinks-manager')}</td>
                    </tr>
                ))}

                </tbody>
            </table>

            {props.data.length === 0 && <div
                className="daim-no-data-found">{__('We couldn\'t find any results matching your filters. Try adjusting your criteria.', 'interlinks-manager')}</div>}
            {props.data.length > 0 &&
                <div className="daim-react-table__pagination-container">
                    <div className='daext-displaying-num'>{props.data.length + ' items'}</div>
                    <Pagination
                        className="pagination-bar"
                        currentPage={currentPage}
                        totalCount={props.data.length}
                        pageSize={PageSize}
                        onPageChange={page => setCurrentPage(page)}
                    />
                </div>
            }

        </div>

    );

};

export default Chart;
