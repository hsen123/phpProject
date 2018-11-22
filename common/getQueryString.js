import {
    FILTER_CITATION_NAME,
    FILTER_PARAMETER_NAME,
} from '../analysis-detail/resultListOptions';

export const combineQueryStrings = (...queryStrings) =>
    queryStrings.filter(v => !!v).join('&');

export const getQueryString = ({
    itemsPerPage,
    page,
    order,
    filters,
    search,
}) => {
    const itemsPerPageQS = itemsPerPage ? encodeItemsPerPage(itemsPerPage) : '';
    const pageQS = page ? encodePage(page) : '';
    const orderQS =
        order && order.field && order.direction ? encodeOrder(order) : '';
    const filterQS = filters ? encodeFilters(filters) : '';
    const searchQS = search ? encodeSearch(search) : '';

    return combineQueryStrings(
        itemsPerPageQS,
        pageQS,
        orderQS,
        filterQS,
        searchQS,
    );
};

export const encodeIds = ids =>
    ids
        .reduce(
            (queryString, id, index) => [
                ...queryString,
                `ids[${index}]=${encodeURIComponent(id)}`,
            ],
            [],
        )
        .join('&');

function convertParameterNameFilter(filter) {
    if (filter.selectedParameter.name === FILTER_PARAMETER_NAME) {
        return {
            ...filter,
            selectedParameter: {
                ...filter.selectedParameter,
                name: FILTER_CITATION_NAME,
            },
        };
    }
    return filter;
}

export const encodeFilters = rawFilters => {
    const filters = Object.values(rawFilters)
        .filter(f => f && Object.keys(f).length)
        .reduce((filters, raw) => {
        		const filter = convertParameterNameFilter(raw);
            const operator = encodeURIComponent(filter.selectedFilter);
            const field = encodeURIComponent(filter.selectedParameter.name);
            const value = encodeURIComponent(filter.filterValue);

            if (!Array.isArray(filters[operator])) {
                filters[operator] = [];
            }
            filters[operator].push({ field, value });

            return filters;
        }, {});

    return Object.entries(filters).reduce(
        (queryString, [operator, filters]) => {
            const filterQueries = filters.map(
                ({ field, value }, index) =>
                    `filter[${operator}][${index}][field]=${field}&filter[${operator}][${index}][value]=${value}`,
            );

            return `${
                queryString ? `${queryString}&` : queryString
            }${filterQueries.join('&')}`;
        },
        '',
    );
};

export const encodeSearch = search => `search=${encodeURIComponent(search)}`;

export const encodePage = page => `page=${encodeURIComponent(page)}`;

export const encodeItemsPerPage = itemsPerPage =>
    `itemsPerPage=${encodeURIComponent(itemsPerPage)}`;

export const encodeOrder = order =>
    `order[${encodeURIComponent(order.field)}]=${encodeURIComponent(
        order.direction === 'ascend' ? 'asc' : 'desc',
    )}`;
