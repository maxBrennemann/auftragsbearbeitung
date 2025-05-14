export const initEventSource = () => {
    const eventSource = new EventSource("/events");
    eventSource.onmessage = e => {
        const data = JSON.parse(e.data);
        console.log(data);
    }
}
