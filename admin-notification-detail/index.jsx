import React from 'react';
import { render } from 'react-dom';
import { DetailNotificationPage } from './DetailNotificationPage';
import '../../css/admin/notification-detail.less';

let notification = {};
let sentDate = null;
let id = null;
if (window.broadcast && window.broadcast.id) {
    sentDate = window.broadcast.sentDate;
    id = window.broadcast.id;
    notification = {
        title: window.broadcast.title,
        text: window.broadcast.content,
    };
    if (window.broadcast.image) {
        const img = new Image();
        const url = `/api/broadcast-image/${window.broadcast.id}`;
        img.crossOrigin = 'Anonymous';
        img.onload = function() {
            const canvas = document.createElement('CANVAS');
            const ctx = canvas.getContext('2d');
            canvas.height = this.naturalHeight;
            canvas.width = this.naturalWidth;
            ctx.drawImage(this, 0, 0);
            notification.base64 = canvas.toDataURL();
            notification.fileName = `${window.origin}${url}`;
            notification.useImage = true;
            initReact();
        };
        img.onerror = function() {
            notification.base64 = null;
            notification.fileName = null;
            notification.useImage = false;
            initReact();
        };
        img.src = url;
    } else {
        notification.useImage = false;
        initReact();
    }
} else {
    initReact();
}

function initReact() {
    render(
        <DetailNotificationPage
            id={id}
            sentDate={sentDate}
            notification={notification}
        />,
        document.getElementById('react-detail-notification'),
    );
}
