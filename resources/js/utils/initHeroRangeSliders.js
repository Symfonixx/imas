let jqueryUiPromise = null;

const DEFAULT_PRICE_STEP = 500_000;

function snapToStep(value, min, max, step) {
    const snapped = min + Math.round((Number(value) - min) / step) * step;
    return Math.min(max, Math.max(min, snapped));
}

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

function removeValueInputs($slider) {
    $slider.find(".first-slider-value, .second-slider-value").remove();
}

function mountValueInputs($slider) {
    const markup =
        "<input type='text' class='first-slider-value' disabled/><input type='text' class='second-slider-value' disabled/>";
    $slider.append(markup);
    return {
        $first: $slider.children(".first-slider-value"),
        $second: $slider.children(".second-slider-value"),
    };
}

function initAreaSlider(
    $slider,
    { min, max, unit, values, onChange },
) {
    if (!$slider.length) {
        return;
    }

    if ($slider.hasClass("ui-slider")) {
        $slider.slider("destroy");
    }

    $slider.empty();
    removeValueInputs($slider);

    const { $first, $second } = mountValueInputs($slider);

    const dataMin = Number(min);
    const dataMax = Number(max);
    const dataUnit = unit || "";

    $slider.slider({
        range: true,
        min: dataMin,
        max: dataMax,
        step: 10,
        values: values ?? [dataMin, dataMax],
        slide(_event, ui) {
            $first.val(`${ui.values[0]} ${dataUnit}`);
            $second.val(`${ui.values[1]} ${dataUnit}`);
            onChange?.(ui.values[0], ui.values[1]);
        },
    });

    const current = $slider.slider("values");
    $first.val(`${current[0]} ${dataUnit}`);
    $second.val(`${current[1]} ${dataUnit}`);
    onChange?.(current[0], current[1]);
}

function initPriceSlider(
    $slider,
    { min, max, unit, values, step = DEFAULT_PRICE_STEP, onChange },
) {
    if (!$slider.length) {
        return;
    }

    if ($slider.hasClass("ui-slider")) {
        $slider.slider("destroy");
    }

    $slider.empty();
    removeValueInputs($slider);

    const { $first, $second } = mountValueInputs($slider);

    const dataMin = Number(min);
    const dataMax = Number(max);
    const dataUnit = unit || "$";
    const dataStep = Number(step) || DEFAULT_PRICE_STEP;

    const format = (n) =>
        dataUnit +
        Number(n)
            .toString()
            .replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");

    const rawValues = values ?? [dataMin, dataMax];
    const snappedValues = [
        snapToStep(rawValues[0], dataMin, dataMax, dataStep),
        snapToStep(rawValues[1], dataMin, dataMax, dataStep),
    ];

    $slider.slider({
        range: true,
        min: dataMin,
        max: dataMax,
        step: dataStep,
        values: snappedValues,
        slide(_event, ui) {
            $first.val(format(ui.values[0]));
            $second.val(format(ui.values[1]));
            onChange?.(ui.values[0], ui.values[1]);
        },
    });

    const current = $slider.slider("values");
    $first.val(format(current[0]));
    $second.val(format(current[1]));
    onChange?.(current[0], current[1]);
}

/**
 * Initialize theme-style area + price range sliders (Find Houses range.js behaviour).
 * Value inputs are appended inside each slider element (same as home hero).
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
    priceStep = DEFAULT_PRICE_STEP,
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
        step: priceStep,
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
        const $slider = $(selector);
        if (!$slider.length) {
            return;
        }
        if ($slider.hasClass("ui-slider")) {
            $slider.slider("destroy");
        }
        $slider.empty();
        removeValueInputs($slider);
    });
}
