const isIE11 = !!window.MSInputMethodContext && !!document.documentMode;

function adjustPath(path) {
    if (!isIE11) {
        return path;
    }
    if (path.indexOf('?') === -1) {
        return `${path}?break-cache=${new Date().getTime()}`;
    } else {
        return `${path}&break-cache=${new Date().getTime()}`;
    }
}

export function fetchAndHandleAuth(path, options) {
    return new Promise((resolve, reject) => {
        fetch(adjustPath(path), {
            cache: 'no-store',
            ...options,
        }).then(res => {
            if ([401, 403].includes(res.status)) {
                window.location = '/login';
                return reject(res);
            }

            if (res.status >= 300) {
                return reject(res);
            }

            resolve(res);
        });
    });
}
