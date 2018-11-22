import React, { Component, Fragment } from 'react';
import PT from 'prop-types';
import Button from 'antd/lib/button';
import plusIcon from '../../images/icons/material-design/white/plus.svg';
import { CreateNotificationModal } from '../admin-notifications/CreateNotificationModal';
import { NotificationsQuery } from '../admin-notifications/containers/NotificationsQuery';

export class DetailNotificationPage extends Component {
    static defaultProps = {
        id: PT.oneOf([PT.number, PT.string]),
        sentDate: PT.number,
        notification: PT.object,
    };

    render() {
        const { notification, sentDate, id } = this.props;
        return (
            <div className="vertical-button-bar">
                <NotificationsQuery
                    doFetch={false}
                    render={(
                        {
                            notifications,
                            pagination,
                            sorter,
                            error,
                            loading,
                            loadingSend,
                            loadingUpdate,
                            loadingDelete,
                            ...restProps
                        },
                        {
                            createNotification,
                            deleteNotification,
                            updateNotification,
                            sendBroadcast,
                            ...restActions
                        },
                    ) => (
                        <Fragment>
                            <CreateNotificationModal
                                notification={notification}
                                onSubmit={(...args) => {
                                    return createNotification(...args).then(
                                        res =>
                                            res && res.id
                                                ? (window.location = `/admin/notification/${
                                                      res.id
                                                  }`)
                                                : (window.location = `/admin/notifications`),
                                    );
                                }}
                            >
                                {({ toggleModal }) => (
                                    <Button
                                        type="primary"
                                        className="create-notification-button right-bar-button"
                                        onClick={() => toggleModal(true)}
                                    >
                                        <img
                                            src={plusIcon}
                                            alt="Create Notification"
                                            className="button-icon "
                                        />
                                        {
                                            window.translations.notifications
                                                .createNewFromThis
                                        }
                                    </Button>
                                )}
                            </CreateNotificationModal>
                            {sentDate ? (
                                <Button
                                    loading={loadingDelete}
                                    className="right-bar-button"
                                    type="primary"
                                    onClick={() =>
                                        deleteNotification(id).then(
                                            () =>
                                                (window.location =
                                                    '/admin/notifications'),
                                        )
                                    }
                                >
                                    {
                                        window.translations.notifications
                                            .deleteNotification
                                    }
                                </Button>
                            ) : (
                                <Fragment>
                                    <CreateNotificationModal
                                        notification={notification}
                                        editMode={true}
                                        onSubmit={updateNotification(id)}
                                    >
                                        {({ toggleModal }) => (
                                            <Button
                                                type="primary"
                                                onClick={() =>
                                                    toggleModal(true)
                                                }
                                            >
                                                {
                                                    window.translations
                                                        .notifications
                                                        .editNotification
                                                }
                                            </Button>
                                        )}
                                    </CreateNotificationModal>
                                    <Button
                                        loading={loadingSend}
                                        type="primary"
                                        onClick={() =>
                                            sendBroadcast(id).then(() => {
                                                window.location = `/admin/notification/${id}`;
                                            })
                                        }
                                    >
                                        {window.translations.notifications.send}
                                    </Button>
                                </Fragment>
                            )}
                        </Fragment>
                    )}
                />
            </div>
        );
    }
}
