import React, { Component } from 'react';
import { fetchAndHandleAuth } from '../../common/fetch';

const timezoneOffset = -new Date().getTimezoneOffset();
const unixTimestamp = Math.floor(Date.now() / 1000);

export class ActivityQuery extends Component {
    static propTypes = {};
    static defaultProps = {};

    state = {
        loading: true,
        data: null,
        error: null,
        timestamp: Date.now(),
    };

    fetchActivity = (
        path = `/api/dashboard/activity?in=${unixTimestamp}&tzo=${timezoneOffset}`,
        timestamp,
    ) => {
        this.setState({ loading: true });
        return fetchAndHandleAuth(path, {
            credentials: 'include',
            headers: {
                accept: 'application/ld+json',
            },
        })
            .then(res => res.json())
            .then(data =>
                this.setState({
                    data,
                    error: null,
                    loading: false,
                    timestamp,
                }),
            )
            .catch(e =>
                this.setState({
                    error: e.message,
                    loading: false,
                }),
            );
    };

    fetchPrevActivity = () =>
        this.fetchActivity(
            this.state.data.prev,
            this.state.data.prev_ts * 1000,
        );

    fetchNextActivity = () =>
        this.fetchActivity(
            this.state.data.next,
            this.state.data.next_ts * 1000,
        );

    componentDidMount() {
        this.fetchActivity();
    }

    actions = {
        fetchActivity: this.fetchActivity,
        fetchPrevActivity: this.fetchPrevActivity,
        fetchNextActivity: this.fetchNextActivity,
    };

    render() {
        const { children } = this.props;
        return children(this.state, this.actions);
    }
}
