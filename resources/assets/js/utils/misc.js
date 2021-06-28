function _getFieldKey(signature) {
    return 'inplace:' + signature;
}

function _hasProperty(obj, prop) {
    const _has = Object.prototype.hasOwnProperty;

    return _has.call(obj, prop)
};

export { _getFieldKey, _hasProperty };