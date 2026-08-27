(function (window, document) {
    "use strict";

    if (window.__flacsoSeminarioAnalyticsBooted) {
        return;
    }
    window.__flacsoSeminarioAnalyticsBooted = true;

    var page = document.querySelector(".flacso-seminario-page");
    if (!page) {
        return;
    }

    var titleNode = document.getElementById("seminario-title");
    var seminarTitle = titleNode ? String(titleNode.textContent || "").trim() : document.title;
    var bodyClass = document.body ? document.body.className : "";
    var postIdMatch = bodyClass.match(/\bpostid-(\d+)\b/);
    var seminarId = postIdMatch ? postIdMatch[1] : "";
    var sentViews = {};

    function baseParams() {
        return {
            content_type: "seminario",
            content_name: seminarTitle,
            content_ids: seminarId ? [seminarId] : [],
            seminario_id: seminarId,
            seminario_nombre: seminarTitle,
            page_location: window.location.href
        };
    }

    function trackMetaAndGoogle(eventName, params) {
        var payload = Object.assign({}, baseParams(), params || {});

        if (typeof window.flacsoMetaTrackCustom === "function") {
            window.flacsoMetaTrackCustom(eventName, payload);
            return;
        }

        if (typeof window.flacsoTrackGA4 === "function") {
            window.flacsoTrackGA4(eventName, payload);
        }
    }

    function markSectionViewed(key, section, eventName, params) {
        if (!section || sentViews[key]) {
            return;
        }

        if (!("IntersectionObserver" in window)) {
            sentViews[key] = true;
            trackMetaAndGoogle(eventName, params);
            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting || entry.intersectionRatio < 0.5 || sentViews[key]) {
                    return;
                }

                sentViews[key] = true;
                trackMetaAndGoogle(eventName, Object.assign({
                    visibility_ratio: Math.round(entry.intersectionRatio * 100) / 100
                }, params || {}));
                observer.disconnect();
            });
        }, {
            threshold: [0.5]
        });

        observer.observe(section);
    }

    function collectPrices(section) {
        if (!section) {
            return {};
        }

        var amounts = Array.prototype.slice.call(section.querySelectorAll(".seminario-amount"));
        var result = {};

        amounts.forEach(function (amount) {
            var currencyNode = amount.querySelector(".seminario-amount-label");
            var valueNode = amount.querySelector(".seminario-amount-value");
            var card = amount.closest(".seminario-investment-card");
            var currency = currencyNode ? String(currencyNode.textContent || "").trim().toLowerCase() : "";
            var value = valueNode ? String(valueNode.textContent || "").trim() : "";
            var tier = card && card.classList.contains("is-discount") ? "comunidad_flacso" : "general";

            if (currency && value) {
                result["precio_" + tier + "_" + currency] = value;
            }
        });

        return result;
    }

    markSectionViewed(
        "general_information",
        document.getElementById("informacion"),
        "seminario_information_view",
        {
            section_name: "informacion_general"
        }
    );

    var investmentSection = document.querySelector(".seminario-section--investment");
    markSectionViewed(
        "prices",
        investmentSection,
        "seminario_price_view",
        Object.assign({
            section_name: "precios"
        }, collectPrices(investmentSection))
    );

    document.addEventListener("click", function (event) {
        var target = event.target && typeof event.target.closest === "function"
            ? event.target.closest(".flacso-seminario-page a, .flacso-seminario-page button")
            : null;

        if (!target) {
            return;
        }

        var isPrimary = target.classList.contains("seminario-btn-primary");
        var isSecondary = target.classList.contains("seminario-btn-secondary");
        var isButtonLike = isPrimary || isSecondary || target.getAttribute("role") === "button" || target.tagName === "BUTTON";

        if (!isButtonLike) {
            return;
        }

        var label = String(target.getAttribute("aria-label") || target.textContent || "").replace(/\s+/g, " ").trim();
        var href = target.tagName === "A" ? target.href : "";
        var placement = target.closest(".seminario-hero-actions") ? "hero" : "body";
        var action = "button";

        if (isPrimary || /preinscripcion|inscrib/i.test(href + " " + label)) {
            action = "preinscripcion";
        } else if (/informaci[oó]n/i.test(label) || href.indexOf("#informacion") !== -1) {
            action = "ver_informacion";
        }

        trackMetaAndGoogle("seminario_cta_click", {
            event_category: "seminario",
            event_label: label,
            cta_action: action,
            cta_label: label,
            cta_placement: placement,
            destination_url: href
        });
    }, true);
})(window, document);
