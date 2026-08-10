(function (global) {
  'use strict';

  const sourceConfig = global.BaraBeatEditionConfig && typeof global.BaraBeatEditionConfig === 'object'
    ? global.BaraBeatEditionConfig
    : {};
  const edition = typeof sourceConfig.edition === 'string' && sourceConfig.edition
    ? sourceConfig.edition
    : 'full';
  const features = sourceConfig.features && typeof sourceConfig.features === 'object'
    ? Object.assign({}, sourceConfig.features)
    : {};
  const content = sourceConfig.content && typeof sourceConfig.content === 'object'
    ? Object.assign({}, sourceConfig.content)
    : {};

  function hasFeature(name) {
    return Object.prototype.hasOwnProperty.call(features, String(name));
  }

  function get(name) {
    const featureName = String(name);
    return hasFeature(featureName) ? features[featureName] : undefined;
  }

  function isEnabled(name) {
    const value = get(name);
    if (value === null) {
      return true;
    }
    if (typeof value === 'boolean') {
      return value;
    }
    if (typeof value === 'number') {
      return value > 0;
    }
    return Boolean(value);
  }

  function checkLimit(name, value) {
    const limit = get(name);
    if (limit === null) {
      return true;
    }
    if (typeof limit !== 'number') {
      return isEnabled(name);
    }

    const numericValue = Number(value);
    return Number.isFinite(numericValue) && numericValue <= limit;
  }

  function showUpgradeInfo(featureName, message) {
    const detail = {
      edition: edition,
      feature: String(featureName || ''),
      message: message || 'Diese Funktion ist in der aktuellen BaraBeat-Edition nicht verfügbar.'
    };

    if (typeof global.CustomEvent === 'function') {
      global.dispatchEvent(new CustomEvent('barabeat-upgrade-info', { detail: detail }));
    }
    return detail;
  }

  function requireFeature(name, message) {
    if (isEnabled(name)) {
      return true;
    }
    showUpgradeInfo(name, message);
    return false;
  }

  global.BaraBeatFeatures = Object.freeze({
    edition: edition,
    features: Object.freeze(features),
    content: Object.freeze(content),
    get: get,
    isEnabled: isEnabled,
    checkLimit: checkLimit,
    require: requireFeature,
    showUpgradeInfo: showUpgradeInfo
  });

  if (typeof global.showUpgradeInfo !== 'function') {
    global.showUpgradeInfo = showUpgradeInfo;
  }

  if (sourceConfig.debug && global.console && typeof global.console.info === 'function') {
    global.console.info('[BaraBeat Edition]', edition, features);
  }
})(window);

