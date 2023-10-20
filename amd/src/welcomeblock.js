import $ from 'jquery';

export const init = () => {
    var firstBlock = $("#block-region-content").find('section');
    if (firstBlock.length > 0) {
        var newSection = document.createElement("div");
        newSection.classList.add('welcome-section');
        $(firstBlock).addClass('side-welcome-section');
        $(newSection).append(firstBlock.first());
        var impostordiv = welcomeBlock();

        $("#block-region-content").prepend(newSection);
        $(newSection).prepend(impostordiv);
    }
};

const welcomeBlock = () => {
    var welcomeBlock = document.createElement("div");
    welcomeBlock.classList.add('welcome-block');
    welcomeBlock.classList.add('card');
    welcomeBlock.classList.add('mr-4');
    welcomeBlock.classList.add('mb-3');
    welcomeBlock.classList.add('px-4');
    welcomeBlock.classList.add('py-5');

    var title = document.createElement("p");
    title.classList.add('welcome-block-title');
    title.textContent = "¡Bienvenido/a, Cindy Peña!";

    var subtitle = document.createElement("p");
    // eslint-disable-next-line max-len
    subtitle.textContent = "Esta es la portada del campus donde se dispone de informacion sintetizada sobre el estado de los estudios y acceso a los cursos.";
    subtitle.classList.add('welcome-back-subtitle');

    var items = document.createElement("div");
    items.classList.add('welcome-block-items');

    items.innerHTML = `
    <div class="mb-3">
        <div class='d-flex align-items-center'>
            <div class='activityiconcontainer collaboration courseicon mr-3'>
                <span class="material-symbols-outlined dark">
                    error
                </span>
            </div>
            <div class='welcome-block-item-text'>
                Revise todas las alertas de la seccion estado.
            </div>
        </div>
    </div>
    <div class="mb-3">
        <div class='d-flex align-items-center'>
            <div class='activityiconcontainer collaboration courseicon mr-3'>
                <span class="material-symbols-outlined dark">
                    person_alert
                </span>
            </div>
            <div class='welcome-block-item-text'>
                Liste los cursos a los que esta matriculado y acceda a ellos.
            </div>
        </div>
    </div>
    <div class="mb-3">
        <div class='d-flex align-items-center'>
            <div class='activityiconcontainer collaboration courseicon mr-3'>
                <span class="material-symbols-outlined dark">
                    monitoring
                </span>
            </div>
            <div class='welcome-block-item-text'>
                Conozca el estado de finalizacion de sus cursos o asignaturas.
            </div>
        </div>
    </div>
    `;

    welcomeBlock.append(title);
    welcomeBlock.append(subtitle);
    welcomeBlock.append(items);
    return welcomeBlock;
};