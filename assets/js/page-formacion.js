(function() {
	// Smooth scroll para botones del hero con anchors
	const links = document.querySelectorAll('.flacso-oferta-hero__actions a[href^="#"]');
	links.forEach(link => {
		link.addEventListener('click', (e) => {
			const href = link.getAttribute('href');
			if (!href || !href.startsWith('#')) return;
			const target = document.querySelector(href);
			if (!target) return;
			e.preventDefault();
			window.scrollTo({ top: target.offsetTop - 96, behavior: 'smooth' });
		});
	});

	// Countdown para fechas de inicio
	const countdowns = document.querySelectorAll('[data-countdown]');
	countdowns.forEach(el => {
		const dateStr = el.getAttribute('data-countdown');
		const normalizedDateStr = /^\d{4}-\d{2}-\d{2}$/.test(dateStr) ? `${dateStr}T00:00:00` : dateStr;
		const target = new Date(normalizedDateStr);
		if (!target.getTime()) return;
		const today = new Date();
		today.setHours(0,0,0,0);
		const diff = Math.ceil((target - today) / (1000*60*60*24));
		const label = el.querySelector('.flacso-oferta-countdown__text');
		if (!label) return;
		if (diff > 0) {
			label.textContent = diff === 1 ? 'Falta 1 día' : 'Faltan ' + diff + ' días';
		} else if (diff === 0) {
			label.textContent = 'Comienza hoy';
		} else {
			el.style.display = 'none';
			el.setAttribute('aria-hidden', 'true');
		}
	});
})();

// Meta Pixel y GA4: ViewContent/view_item_list al cargar + click tracking en botones y cards
(function() {
	function mapGA4EventName(eventName) {
		if (eventName === "OfertaAcademicaClick") return "select_item";
		if (eventName === "ConvenioClick") return "convenio_click";
		if (eventName === "SeminarioClick") return "seminario_click";
		return eventName;
	}

	function trackGA4(eventName, params) {
		if (typeof window.flacsoTrackGA4 === "function") {
			window.flacsoTrackGA4(eventName, params || {});
			return;
		}
		if (typeof window.gtag === "function") {
			window.gtag("event", eventName, params || {});
		}
	}

	var listPayload = {
		content_name: "Listado oferta academica completa",
		content_category: "oferta_academica",
		content_type: "oferta_academica",
		item_list_name: "Oferta academica completa"
	};

	if (typeof window.flacsoMetaTrack === "function") {
		window.flacsoMetaTrack("ViewContent", listPayload);
	} else {
		trackGA4("view_item_list", listPayload);
	}

	var clickTargets = document.querySelectorAll(
		".flacso-oferta-hero__btn, .flacso-oa-card, .flacso-oferta-section__btn"
	);

	clickTargets.forEach(function(el) {
		el.addEventListener("click", function() {
			var eventName = "OfertaAcademicaClick";
			var href = el.getAttribute("href") || "";
			var title = (el.innerText || "").trim();
			var category = "oferta_academica";

			if (href.indexOf("convenio") !== -1 || el.classList.contains("flacso-oferta-hero__btn--convenios")) {
				eventName = "ConvenioClick";
				category = "convenios";
			} else if (href.indexOf("seminario") !== -1 || el.closest("#seminarios")) {
				eventName = "SeminarioClick";
				category = "seminarios";
			}

			var cardTitle = el.querySelector(".flacso-oa-card__title");
			if (cardTitle) {
				title = cardTitle.innerText.trim();
			}

			var clickPayload = {
				content_name: title,
				link_url: href,
				content_category: category,
				item_name: title,
				item_category: category,
				item_list_name: "Oferta academica completa"
			};

			if (typeof window.flacsoMetaTrackCustom === "function") {
				window.flacsoMetaTrackCustom(eventName, clickPayload);
			} else {
				trackGA4(mapGA4EventName(eventName), clickPayload);
			}
		});
	});
})();
