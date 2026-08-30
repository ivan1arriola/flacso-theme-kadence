<?php
/**
 * Template: Consulta sobre Seminarios
 *
 * Formulario para contactar sobre seminarios específicos
 * URL: /formacion/contactar-seminario/?ID=seminario_id
 * o /formacion/contactar-seminario/ para seleccionar seminario
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Obtener ID del seminario desde parámetro ?ID= (como en preinscripción)
$seminario_id = isset( $_GET['ID'] ) ? absint( wp_unslash( $_GET['ID'] ) ) : 0;
$seminario_seleccionado = null;
$solo_un_seminario = false;

// Aceptar tanto OfertaAcademica tipo seminario como el CPT legacy durante Release A.
if ($seminario_id > 0 && function_exists('flacso_theme_get_seminar_context')) {
    $selected_context = flacso_theme_get_seminar_context($seminario_id);
    if (!empty($selected_context) && get_post_status($seminario_id) === 'publish') {
        $seminario_seleccionado = get_post($seminario_id);
        $solo_un_seminario = (bool) $seminario_seleccionado;
    }
}

// Si no hay seminario seleccionado, usar el catálogo normalizado.
$seminarios_disponibles = [];
if (!$solo_un_seminario) {
    $catalog = function_exists('flacso_theme_get_seminars_catalog')
        ? flacso_theme_get_seminars_catalog()
        : [];

    foreach ($catalog as $context) {
        $post_id = (int) ($context['offer_id'] ?? 0);
        if ($post_id <= 0) {
            continue;
        }
        $imagen = (string) ($context['thumbnail_url'] ?? '');
        if ($imagen === '') {
            $imagen = 'https://via.placeholder.com/300x200/16396f/ffffff?text=Seminario';
        }
        $seminarios_disponibles[] = [
            'id'     => $post_id,
            'titulo' => (string) ($context['title'] ?? get_the_title($post_id)),
            'imagen' => $imagen,
            'fecha'  => (string) ($context['instance']['fecha_inicio'] ?? ''),
        ];
    }

    usort($seminarios_disponibles, static function (array $a, array $b): int {
        if ($a['fecha'] === $b['fecha']) {
            return strcasecmp($a['titulo'], $b['titulo']);
        }
        if ($a['fecha'] === '') {
            return 1;
        }
        if ($b['fecha'] === '') {
            return -1;
        }
        return strcmp($a['fecha'], $b['fecha']);
    });
}

// Función para obtener lista de países
function flacso_get_paises_consulta() {
    return [
        'AR' => 'Argentina',
        'BO' => 'Bolivia',
        'BR' => 'Brasil',
        'CL' => 'Chile',
        'CO' => 'Colombia',
        'CR' => 'Costa Rica',
        'CU' => 'Cuba',
        'DO' => 'República Dominicana',
        'EC' => 'Ecuador',
        'SV' => 'El Salvador',
        'GT' => 'Guatemala',
        'HN' => 'Honduras',
        'MX' => 'México',
        'NI' => 'Nicaragua',
        'PA' => 'Panamá',
        'PY' => 'Paraguay',
        'PE' => 'Perú',
        'PR' => 'Puerto Rico',
        'UY' => 'Uruguay',
        'VE' => 'Venezuela',
        'AT' => 'Austria',
        'BE' => 'Bélgica',
        'CZ' => 'República Checa',
        'DK' => 'Dinamarca',
        'EE' => 'Estonia',
        'FI' => 'Finlandia',
        'FR' => 'Francia',
        'DE' => 'Alemania',
        'GR' => 'Grecia',
        'HU' => 'Hungría',
        'IE' => 'Irlanda',
        'IT' => 'Italia',
        'LV' => 'Letonia',
        'LT' => 'Lituania',
        'LU' => 'Luxemburgo',
        'NL' => 'Países Bajos',
        'PL' => 'Polonia',
        'PT' => 'Portugal',
        'RO' => 'Rumania',
        'SK' => 'Eslovaquia',
        'SI' => 'Eslovenia',
        'ES' => 'España',
        'SE' => 'Suecia',
        'CH' => 'Suiza',
        'GB' => 'Reino Unido',
        'AU' => 'Australia',
        'CN' => 'China',
        'IN' => 'India',
        'JP' => 'Japón',
        'KR' => 'Corea del Sur',
        'NZ' => 'Nueva Zelanda',
        'RU' => 'Rusia',
        'ZA' => 'Sudáfrica',
        'US' => 'Estados Unidos',
        'CA' => 'Canadá',
    ];
}

?>

<main id="main" class="site-main flacso-consulta-seminario-main">
<div class="content-container site-container my-5 py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h1 class="h3 mb-2">Contactanos sobre nuestros seminarios</h1>
                    <p class="mb-0 opacity-75">Completa el formulario y nos pondremos en contacto a la brevedad</p>
                </div>

                <div class="card-body p-4 p-md-5">
                    <form id="formulario-consulta-seminario" novalidate>

                        <!-- SECCIÓN: Selección de Seminario -->
                        <div class="mb-5">
                            <?php if ($solo_un_seminario && $seminario_seleccionado): ?>
                                <!-- Un seminario preseleccionado -->
                                <label class="form-label fw-bold fs-5 d-block mb-3">Estás consultando por:</label>
                                <div class="card border-primary bg-light">
                                    <div class="card-body text-center p-4">
                                        <?php
                                        $img = has_post_thumbnail($seminario_seleccionado->ID)
                                            ? get_the_post_thumbnail_url($seminario_seleccionado->ID, 'medium')
                                            : 'https://via.placeholder.com/300x200/16396f/ffffff?text=Seminario';
                                        ?>
                                        <img src="<?php echo esc_url($img); ?>"
                                             alt="<?php echo esc_attr($seminario_seleccionado->post_title); ?>"
                                             class="img-fluid rounded mb-3"
                                             style="max-height: 150px; width: auto; object-fit: cover;">
                                        <h5 class="text-dark fw-bold mb-0"><?php echo esc_html($seminario_seleccionado->post_title); ?></h5>
                                    </div>
                                </div>
                                <input type="hidden" name="seminario_id" value="<?php echo (int)$seminario_seleccionado->ID; ?>">
                                <input type="hidden" name="seminario_titulo" value="<?php echo esc_attr($seminario_seleccionado->post_title); ?>">

                            <?php elseif (!empty($seminarios_disponibles)): ?>
                                <!-- Grid de selección múltiple -->
                                <label class="form-label fw-bold fs-5 d-block mb-3">Selecciona el seminario sobre el que deseas consultar *</label>
                                <div class="row g-3" id="seminarios-grid">
                                    <?php foreach ($seminarios_disponibles as $seminario): ?>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="seminario-card-consulta card h-100 border-2 shadow-sm cursor-pointer">
                                                <input type="radio" name="seminario_id"
                                                       id="seminario-<?php echo $seminario['id']; ?>"
                                                       value="<?php echo (int)$seminario['id']; ?>"
                                                       data-titulo="<?php echo esc_attr($seminario['titulo']); ?>"
                                                       class="d-none" required>
                                                <label for="seminario-<?php echo $seminario['id']; ?>" class="card-body text-center p-3 m-0 d-flex flex-column h-100">
                                                    <img src="<?php echo esc_url($seminario['imagen']); ?>"
                                                         alt="<?php echo esc_attr($seminario['titulo']); ?>"
                                                         class="img-fluid rounded mb-3"
                                                         style="height: 120px; width: 100%; object-fit: cover;">
                                                    <h6 class="card-title mb-0 text-dark flex-grow-1 d-flex align-items-center justify-content-center">
                                                        <?php echo esc_html($seminario['titulo']); ?>
                                                    </h6>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="invalid-feedback" id="error-seminario" style="display: none; margin-top: 10px;">
                                    Por favor selecciona un seminario.
                                </div>
                                <div class="form-text mt-2">Haz clic en el seminario sobre el que deseas consultar</div>

                            <?php else: ?>
                                <div class="alert alert-warning text-center">
                                    <p class="mb-0">No hay seminarios disponibles en este momento.</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- SECCIÓN: Datos personales -->
                        <?php if (!empty($seminarios_disponibles) || $solo_un_seminario): ?>

                        <div class="row g-3">
                            <!-- Nombre -->
                            <div class="col-md-6">
                                <label for="nombre" class="form-label fw-bold">Nombre y Apellido *</label>
                                <input type="text" class="form-control form-control-lg" name="nombre" id="nombre"
                                       placeholder="Tu nombre completo" required>
                                <div class="invalid-feedback">Por favor ingresa tu nombre.</div>
                            </div>

                            <!-- Correo -->
                            <div class="col-md-6">
                                <label for="correo" class="form-label fw-bold">Correo Electrónico *</label>
                                <input type="email" class="form-control form-control-lg" name="correo" id="correo"
                                       placeholder="tu.email@ejemplo.com" required>
                                <div class="invalid-feedback">Por favor ingresa un correo válido.</div>
                            </div>

                            <!-- Teléfono -->
                            <div class="col-md-6">
                                <label for="telefono" class="form-label fw-bold">Teléfono *</label>
                                <input type="tel" class="form-control form-control-lg" name="telefono" id="telefono" required>
                                <div class="invalid-feedback">Por favor ingresa un número válido.</div>
                                <div id="error-msg" class="text-danger mt-1" style="display:none;"></div>
                                <div id="valid-msg" class="text-success mt-1" style="display:none;"></div>
                            </div>

                            <!-- País -->
                            <div class="col-md-6">
                                <label for="pais" class="form-label fw-bold">País *</label>
                                <input type="text" class="form-control form-control-lg" name="pais" id="pais" required>
                                <div class="invalid-feedback">Por favor selecciona un país.</div>
                            </div>

                            <!-- Consulta -->
                            <div class="col-12">
                                <label for="consulta" class="form-label fw-bold">Tu Consulta *</label>
                                <textarea class="form-control form-control-lg" name="consulta" id="consulta"
                                          rows="5" placeholder="Cuéntanos tu consulta, pregunta o comentario..." required></textarea>
                                <div class="invalid-feedback">Por favor ingresa tu consulta.</div>
                            </div>
                        </div>

                        <!-- Botón de envío -->
                        <div class="mt-5">
                            <button type="submit" class="btn btn-primary btn-lg w-100 py-3 fw-bold">
                                <span class="submit-text">Enviar Consulta</span>
                                <span class="loading-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                    Enviando...
                                </span>
                            </button>
                        </div>

                        <!-- Resultado -->
                        <div id="resultado-consulta" class="mt-4" aria-live="polite" role="status"></div>

                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación -->
</main>
<div class="modal fade" id="modalConfirmacionConsulta" tabindex="-1" aria-labelledby="modalConfirmacionLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalConfirmacionLabel">Confirma tu consulta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="modal-consulta-body">
                <!-- Datos de confirmación se inyectan aquí -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Corregir</button>
                <button type="button" class="btn btn-primary" id="btn-confirmar-consulta">Confirmar y Enviar</button>
            </div>
        </div>
    </div>
</div>


<script>
    jQuery(document).ready(function($) {
        const trackMetaEvent = (eventName, params) => {
            if (typeof window.flacsoMetaTrack !== "function") {
                return;
            }
            try {
                window.flacsoMetaTrack(eventName, params || {});
            } catch (e) {
                console.warn("[Consulta Seminario] Error enviando evento Meta Pixel:", e);
            }
        };

        const form = $("#formulario-consulta-seminario");
        const nombreInput = $("#nombre");
        const correoInput = $("#correo");
        const telefonoInput = $("#telefono");
        const paisInput = $("#pais");
        const consultaInput = $("#consulta");
        const seminarioInput = $('input[name="seminario_id"]');
        const errorMsg = $("#error-msg");
        const validMsg = $("#valid-msg");
        const resultado = $("#resultado-consulta");
        const submitBtn = form.find('button[type="submit"]');
        const errorSeminario = $("#error-seminario");

        // ==== INICIALIZACIÓN DE INTL-TEL-INPUT ====
        const iti = window.intlTelInput(telefonoInput[0], {
            initialCountry: "uy",
            preferredCountries: ["uy", "ar", "br", "cl", "co"],
            separateDialCode: true,
            nationalMode: false,
            utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@24.2.0/build/js/utils.js"
        });

        const errorMap = ["Número inválido", "Código de país inválido", "Demasiado corto", "Demasiado largo", "Número inválido"];

        // ==== INICIALIZACIÓN DE COUNTRY SELECT ====
        paisInput.countrySelect({
            defaultCountry: "uy",
            preferredCountries: ["uy", "ar", "br", "cl", "co"]
        });

        // ==== FUNCIONES DE VALIDACIÓN ====
        const resetTelefono = () => {
            telefonoInput.removeClass("is-invalid is-valid");
            errorMsg.hide().text("");
            validMsg.hide().text("");
        };

        const validarCampo = (campo, tipo) => {
            let valido = true;

            if (tipo === "nombre") {
                valido = campo.val().trim() !== "";
                campo.toggleClass("is-invalid", !valido);
                campo.toggleClass("is-valid", valido);
            } else if (tipo === "correo") {
                const emailVal = campo.val().trim();
                valido = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal);
                campo.toggleClass("is-invalid", !valido);
                campo.toggleClass("is-valid", valido);
            } else if (tipo === "telefono") {
                resetTelefono();
                const val = campo.val().trim();
                if (!val) {
                    valido = false;
                    errorMsg.text("Campo obligatorio").show();
                } else if (!iti.isValidNumber()) {
                    valido = false;
                    const code = iti.getValidationError();
                    errorMsg.text(errorMap[code] || "Número inválido").show();
                } else {
                    valido = true;
                    validMsg.text("✓ Número válido").show();
                }
                campo.toggleClass("is-invalid", !valido);
                campo.toggleClass("is-valid", valido);
            } else if (tipo === "pais") {
                valido = campo.val().trim() !== "";
                campo.toggleClass("is-invalid", !valido);
                campo.toggleClass("is-valid", valido);
            } else if (tipo === "consulta") {
                valido = campo.val().trim() !== "";
                campo.toggleClass("is-invalid", !valido);
                campo.toggleClass("is-valid", valido);
            } else if (tipo === "seminario") {
                <?php if (!$solo_un_seminario): ?>
                valido = seminarioInput.filter(':checked').length > 0;
                if (!valido) {
                    errorSeminario.show();
                } else {
                    errorSeminario.hide();
                }
                <?php else: ?>
                valido = true;
                <?php endif; ?>
            }

            return valido;
        };

        // ==== EVENT LISTENERS ====
        nombreInput.on("input blur", () => validarCampo(nombreInput, "nombre"));
        correoInput.on("input blur", () => validarCampo(correoInput, "correo"));
        telefonoInput.on("input blur change", () => validarCampo(telefonoInput, "telefono"));
        paisInput.on("change keyup blur", () => validarCampo(paisInput, "pais"));
        consultaInput.on("input blur", () => validarCampo(consultaInput, "consulta"));

        <?php if (!$solo_un_seminario): ?>
        seminarioInput.on("change", function() {
            validarCampo($(this), "seminario");
        });
        <?php endif; ?>

        // ==== SUBMIT DEL FORMULARIO ====
        form.on("submit", function(e) {
            e.preventDefault();

            // Validar todos los campos
            const nombreVal = validarCampo(nombreInput, "nombre");
            const correoVal = validarCampo(correoInput, "correo");
            const telefonoVal = validarCampo(telefonoInput, "telefono");
            const paisVal = validarCampo(paisInput, "pais");
            const consultaVal = validarCampo(consultaInput, "consulta");
            const seminarioVal = validarCampo(seminarioInput, "seminario");

            if (!nombreVal || !correoVal || !telefonoVal || !paisVal || !consultaVal || !seminarioVal) {
                let mensajesError = [];
                if (!seminarioVal) mensajesError.push("• Seminario");
                if (!nombreVal) mensajesError.push("• Nombre y apellido");
                if (!correoVal) mensajesError.push("• Correo electrónico");
                if (!telefonoVal) mensajesError.push("• Teléfono");
                if (!paisVal) mensajesError.push("• País");
                if (!consultaVal) mensajesError.push("• Consulta");

                resultado.html(`
                    <div class="alert alert-danger">
                        <strong>Por favor completa los siguientes campos:</strong>
                        <ul class="mb-0 mt-2">
                            ${mensajesError.map(msg => `<li>${msg}</li>`).join('')}
                        </ul>
                    </div>
                `);

                $('html, body').animate({
                    scrollTop: resultado.offset().top - 100
                }, 500);
                return;
            }

            // Obtener título del seminario
            <?php if ($solo_un_seminario): ?>
            const seminarioTitulo = "<?php echo esc_js($seminario_seleccionado->post_title); ?>";
            <?php else: ?>
            const seminarioTitulo = seminarioInput.filter(':checked').data('titulo');
            <?php endif; ?>

            const datosFormulario = {
                seminario_id: seminarioInput.filter(':checked').length > 0 ? seminarioInput.filter(':checked').val() : (<?php echo $seminario_id; ?> || null),
                seminario_titulo: seminarioTitulo,
                nombre: nombreInput.val().trim(),
                correo: correoInput.val().trim(),
                telefono: iti.getNumber(),
                pais: paisInput.countrySelect("getSelectedCountryData").name,
                consulta: consultaInput.val().trim()
            };

            // Mostrar modal de confirmación
            $("#modal-consulta-body").html(`
                <div class="mb-3">
                    <strong>Seminario:</strong><br>
                    <span class="text-muted">${datosFormulario.seminario_titulo}</span>
                </div>
                <div class="mb-3">
                    <strong>Nombre:</strong><br>
                    <span class="text-muted">${datosFormulario.nombre}</span>
                </div>
                <div class="mb-3">
                    <strong>Correo:</strong><br>
                    <span class="text-muted">${datosFormulario.correo}</span>
                </div>
                <div class="mb-3">
                    <strong>Teléfono:</strong><br>
                    <span class="text-muted">${datosFormulario.telefono}</span>
                </div>
                <div class="mb-3">
                    <strong>País:</strong><br>
                    <span class="text-muted">${datosFormulario.pais}</span>
                </div>
                <div class="mb-3">
                    <strong>Consulta:</strong><br>
                    <span class="text-muted">${datosFormulario.consulta}</span>
                </div>
            `);

            const modal = new bootstrap.Modal(document.getElementById('modalConfirmacionConsulta'));
            modal.show();

            // Guardar datos para confirmar después
            window.datosConsultaSeminario = datosFormulario;
        });

        // ==== CONFIRMAR ENVÍO ====
        $("#btn-confirmar-consulta").on("click", function() {
            bootstrap.Modal.getInstance(document.getElementById('modalConfirmacionConsulta')).hide();

            // Mostrar estado de carga
            submitBtn.find('.submit-text').addClass('d-none');
            submitBtn.find('.loading-spinner').removeClass('d-none');
            submitBtn.prop('disabled', true);

            resultado.html('<div class="alert alert-info text-center">⏳ Enviando tu consulta...</div>');

            // Enviar datos al endpoint REST
            const restUrl = '<?php echo rest_url("flacso/v1/consulta-seminario"); ?>';
            const headers = {
                "Content-Type": "application/json",
                "X-WP-Nonce": "<?php echo wp_create_nonce('wp_rest'); ?>"
            };

            fetch(restUrl, {
                method: "POST",
                headers: headers,
                body: JSON.stringify(window.datosConsultaSeminario)
            })
            .then(res => {
                if (!res.ok) {
                    return res.text().then(text => {
                        let errorData;
                        try {
                            errorData = JSON.parse(text);
                        } catch (e) {
                            errorData = { message: text || 'Error desconocido' };
                        }
                        throw new Error(errorData.message || `HTTP error! status: ${res.status}`);
                    });
                }
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    const editorOk = data.data && data.data.editor_response && data.data.editor_response.ok === true;
                    if (!editorOk) {
                        throw new Error('El CRM no confirmó el guardado de la consulta.');
                    }

                    const seminarioId = window.datosConsultaSeminario.seminario_id;
                    const seminarioTitulo = window.datosConsultaSeminario.seminario_titulo || "";
                    const pixelPayload = {
                        lead_type: "consulta_wordpress_seminario",
                        form_type: "consulta_seminario",
                        lead_source: "wordpress_form",
                        lead_context: "seminario",
                        content_name: seminarioTitulo,
                        content_category: "seminario",
                        content_type: "seminario",
                        content_ids: seminarioId ? ["seminario-" + seminarioId] : [],
                        seminario_id: seminarioId,
                        seminario_titulo: seminarioTitulo,
                        flacso_stage: "consulta_enviada",
                        status: "submitted"
                    };
                    trackMetaEvent("Lead", pixelPayload);

                    resultado.html(`
                        <div class="alert alert-success text-center">
                            <div class="mb-3">
                                <i class="fas fa-check-circle fa-3x"></i>
                            </div>
                            <h4 class="mb-3">✅ ¡Gracias, ${window.datosConsultaSeminario.nombre}!</h4>
                            <p class="mb-2">Hemos recibido tu consulta sobre <strong>"${window.datosConsultaSeminario.seminario_titulo}"</strong>.</p>
                            <p class="mb-0">Te contactaremos a la brevedad en el correo <strong>${window.datosConsultaSeminario.correo}</strong>.</p>
                        </div>
                    `);
                    form[0].reset();
                    resetTelefono();
                    iti.setCountry("uy");
                    paisInput.countrySelect("selectCountry", "uy");
                } else {
                    throw new Error(data.message || 'Error desconocido del servidor');
                }
            })
            .catch((error) => {
                let errorMessage = "❌ Error de conexión. Por favor, intenta nuevamente.";
                if (error.message) {
                    errorMessage = `❌ ${error.message}`;
                }
                resultado.html(`<div class="alert alert-danger">${errorMessage}</div>`);
            })
            .finally(() => {
                submitBtn.find('.submit-text').removeClass('d-none');
                submitBtn.find('.loading-spinner').addClass('d-none');
                submitBtn.prop('disabled', false);
            });
        });
    });
</script>

<?php
get_footer();
