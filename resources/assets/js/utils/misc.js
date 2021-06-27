function getFieldKey(signature) {
    return 'inplace:' + signature;
}

function hasProperty(obj, prop) {
    const _has = Object.prototype.hasOwnProperty;

    return _has.call(obj, prop)
};

export { getFieldKey, hasProperty };