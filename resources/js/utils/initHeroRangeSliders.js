let jqueryUiPromise = null;

/**
 * Load Find Houses jQuery UI (slider widget) once.
 */
export function loadJqueryUi(themeUrl) {
    if (window.jQuery?.fn?.slider) {
        return Promise.resolve();
    }

    if (jqueryUiPromise) {
        return jqueryUiPromise;
    }

    jqueryUiPromise = new Promise((resolve, reject) => {
        const script = document.createElement("script");
        script.src = `${themeUrl}/js/jquery-ui.js`;
        script.async = true;
        script.onload = () => resolve();
        script.onerror = () =>
            reject(new Error("Failed to load jQuery UI for range sliders"));
        document.body.appendChild(script);
    });

    return jqueryUiPromise;
}

function initAreaSlider($el, { min, max, unit, values, onChange }) {
    if (!$el.length) {
        return;
    }

    if ($el.hasClass("ui-slider")) {
        $el.slider("destroy");
    }

    $el.empty();
    $el.append(
        "<input type='text' class='first-slider-value' disabled/><input type='text' class='second-slider-value' disabled/>",
    );

    const dataMin = Number(min);
    const dataMax = Number(max);
    const dataUnit = unit || "";

    $el.slider({
        range: true,
        min: dataMin,
        max: dataMax,
        step: 10,
        values: values ?? [dataMin, dataMax],
        slide(_event, ui) {
            $el.children(".first-slider-value").val(
                `${ui.values[0]} ${dataUnit}`,
            );
            $el.children(".second-slider-value").val(
                `${ui.values[1]} ${dataUnit}`,
            );
            onChange?.(ui.values[0], ui.values[1]);
        },
    });

    const current = $el.slider("values");
    $el.children(".first-slider-value").val(`${current[0]} ${dataUnit}`);
    $el.children(".second-slider-value").val(`${current[1]} ${dataUnit}`);
    onChange?.(current[0], current[1]);
}

function initPriceSlider($el, { min, max, unit, values, onChange }) {
    if (!$el.length) {
        return;
    }

    if ($el.hasClass("ui-slider")) {
        $el.slider("destroy");
    }

    $el.empty();
    $el.append(
        "<input type='text' class='first-slider-value' disabled/><input type='text' class='second-slider-value' disabled/>",
    );

    const dataMin = Number(min);
    const dataMax = Number(max);
    const dataUnit = unit || "$";

    const format = (n) =>
        dataUnit +
        Number(n)
            .toString()
            .replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");

    $el.slider({
        range: true,
        min: dataMin,
        max: dataMax,
        values: values ?? [dataMin, dataMax],
        slide(_event, ui) {
            $el.children(".first-slider-value").val(format(ui.values[0]));
            $el.children(".second-slider-value").val(format(ui.values[1]));
            onChange?.(ui.values[0], ui.values[1]);
        },
    });

    const current = $el.slider("values");
    $el.children(".first-slider-value").val(format(current[0]));
    $el.children(".second-slider-value").val(format(current[1]));
    onChange?.(current[0], current[1]);
}

/**
 * Initialize theme-style area + price range sliders (Find Houses range.js behaviour).
 */
export function initHeroRangeSliders({
    areaSelector = "#imas-hero-area-range",
    priceSelector = "#imas-hero-price-range",
    areaMin = 0,
    areaMax = 1000,
    areaUnit = "m²",
    priceMin = 0,
    priceMax = 600000,
    priceUnit = "$",
    initialArea = null,
    initialPrice = null,
    onAreaChange,
    onPriceChange,
} = {}) {
    const $ = window.jQuery;
    if (!$?.fn?.slider) {
        return;
    }

    initAreaSlider($(areaSelector), {
        min: areaMin,
        max: areaMax,
        unit: areaUnit,
        values: initialArea,
        onChange: onAreaChange,
    });

    initPriceSlider($(priceSelector), {
        min: priceMin,
        max: priceMax,
        unit: priceUnit,
        values: initialPrice,
        onChange: onPriceChange,
    });
}

export function destroyHeroRangeSliders({
    areaSelector = "#imas-hero-area-range",
    priceSelector = "#imas-hero-price-range",
} = {}) {
    const $ = window.jQuery;
    if (!$?.fn?.slider) {
        return;
    }

    [areaSelector, priceSelector].forEach((selector) => {
        const $el = $(selector);
        if ($el.length && $el.hasClass("ui-slider")) {
            $el.slider("destroy");
            $el.empty();
        }
    });
}
