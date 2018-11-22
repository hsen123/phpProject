import React, { Fragment } from 'react';
import { Citation } from '../common/components/Citation';
const splitKind = translations.users.table.resultsSpecial.split('%kind%');

export const columns = [
    {
        key: 'id',
        dataIndex: 'id',
        title: translations.users.table.profileImage,
        render: (id, row) => (
            <span>
                <a href={`/profile/${row.id}`}>
                    <img
                        className="profile-preview"
                        src={`/api/image/user/${row.id}`}
                    />
                </a>
            </span>
        ),
        sorter: true,
        width: '155px',
    },
    {
        key: 'displayName',
        dataIndex: 'displayName',
        title: translations.users.table.name,
        render: (name, row) => (
            <span className="merck-font name">
                <a href={`/profile/${row.id}`}>{!name ? row.email : name}</a>
            </span>
        ),
        sorter: true,
        width: '155px',
    },
    {
        key: 'email',
        dataIndex: 'email',
        title: translations.users.table.email,
        sorter: true,
    },
    {
        key: 'countOfMeasurements',
        dataIndex: 'countOfMeasurements',
        title: translations.users.table.results,
        sorter: true,
    },
    {
        key: 'countOfPh',
        dataIndex: 'countOfPh',
        title: (
            <Fragment>
                {Array.isArray(splitKind) &&
                    splitKind.length === 2 &&
                    splitKind[0]}
                <Citation key="citationPh" constant={1} />
                {Array.isArray(splitKind) &&
                    splitKind.length === 2 &&
                    splitKind[1]}
            </Fragment>
        ),
        sorter: true,
    },
    {
        key: 'countOfNO3',
        dataIndex: 'countOfNO3',
        title: (
            <Fragment>
                {Array.isArray(splitKind) &&
                    splitKind.length === 2 &&
                    splitKind[0]}
                <Citation key="citationNitrate" constant={0} />
                {Array.isArray(splitKind) &&
                    splitKind.length === 2 &&
                    splitKind[1]}
            </Fragment>
        ),
        sorter: true,
    },
    {
        key: 'company',
        dataIndex: 'company',
        title: translations.users.table.company,
        sorter: true,
    },
    {
        key: 'companyAdress',
        dataIndex: 'companyAdress',
        title: translations.users.table.address,
        sorter: true,
    },
    {
        key: 'companyCity',
        dataIndex: 'companyCity',
        title: translations.users.table.city,
        sorter: true,
    },
    {
        key: 'companyPostalCode',
        dataIndex: 'companyPostalCode',
        title: translations.users.table.zipCode,
        sorter: true,
    },
    {
        key: 'companyCountry',
        dataIndex: 'companyCountry',
        title: translations.users.table.country,
        sorter: true,
    },
    {
        key: 'segment',
        dataIndex: 'segment',
        title: translations.users.table.segment,
        sorter: true,
        render: (segment, row) =>
            Number.isNaN(segment)
                ? ''
                : window.translations.analysisDetail.segments[segment],
    },
    {
        key: 'segmentDepartment',
        dataIndex: 'segmentDepartment',
        title: translations.users.table.department,
        sorter: true,
    },
    {
        key: 'segmentWorkgroup',
        dataIndex: 'segmentWorkgroup',
        title: translations.users.table.workGroup,
        sorter: true,
    },
];

export const filterColumns = [
    {
        name: 'displayName',
        label: translations.users.table.name,
        type: 'string',
    },
    {
        name: 'email',
        label: translations.users.table.email,
        type: 'string',
    },
    {
        name: 'countOfMeasurements',
        label: translations.users.table.results,
        type: 'number',
    },
    {
        name: 'countOfNO3',
        label: (
            <Fragment>
                {Array.isArray(splitKind) &&
                    splitKind.length === 2 &&
                    splitKind[0]}
                <Citation key="citationNitrate" constant={0} />
                {Array.isArray(splitKind) &&
                    splitKind.length === 2 &&
                    splitKind[1]}
            </Fragment>
        ),
        type: 'number',
    },
    {
        name: 'countOfPh',
        label: (
            <Fragment>
                {Array.isArray(splitKind) &&
                    splitKind.length === 2 &&
                    splitKind[0]}
                <Citation key="citationNitrate" constant={1} />
                {Array.isArray(splitKind) &&
                    splitKind.length === 2 &&
                    splitKind[1]}
            </Fragment>
        ),
        type: 'number',
    },
    {
        name: 'company',
        label: translations.users.table.company,
        type: 'string',
    },
    {
        name: 'companyAdress',
        label: translations.users.table.address,
        type: 'string',
    },
    {
        name: 'companyCity',
        label: translations.users.table.city,
        type: 'string',
    },
    {
        name: 'companyPostalCode',
        label: translations.users.table.zipCode,
        type: 'string',
    },
    {
        name: 'companyCountry',
        label: translations.users.table.country,
        type: 'string',
    },
    {
        name: 'segment',
        label: translations.users.table.segment,
        type: 'number',
    },
    {
        name: 'segmentDepartment',
        label: translations.users.table.department,
        type: 'string',
    },
    {
        name: 'segmentWorkgroup',
        label: translations.users.table.workGroup,
        type: 'string',
    },
];

export function transformData(data) {
    return data['hydra:member'];
}
