const storage = {
    disk: window.sessionStorage,
    get(key) {
        const currData = this.disk.getItem(key);
        const currDataJson = currData !== null ? JSON.parse(currData) : {};

        return currDataJson;
    },
    set(key, value) {
        const currDataJson = this.get(key);

        this.disk.setItem(key, 
            JSON.stringify(Object.assign({}, currDataJson, value))
        );
    }
}

export default storage;