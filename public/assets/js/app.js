(function () {
    'use strict';

    var toggle = document.querySelector('.nav-toggle');
    var nav = document.getElementById('site-nav');
    if (toggle && nav) {
        toggle.addEventListener('click', function () {
            var open = toggle.getAttribute('aria-expanded') === 'true';
            toggle.setAttribute('aria-expanded', String(!open));
            nav.classList.toggle('is-open', !open);
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && nav.classList.contains('is-open')) {
                toggle.setAttribute('aria-expanded', 'false');
                nav.classList.remove('is-open');
                toggle.focus();
            }
        });
    }

    document.querySelectorAll('.ba').forEach(function (ba) {
        var range = ba.querySelector('.ba-range');
        if (!range) {
            return;
        }
        var update = function () {
            ba.style.setProperty('--pos', range.value + '%');
            range.setAttribute('aria-valuetext', range.value + '% na');
        };
        range.addEventListener('input', update);
        update();
    });

    document.querySelectorAll('.ruler[data-max]').forEach(function (ruler) {
        var max = parseFloat(ruler.getAttribute('data-max')) || 25;
        var scale = ruler.querySelector('.ruler-scale');
        var items = ruler.querySelectorAll('.ruler-towns li');
        if (!scale || items.length < 2) {
            return;
        }
        items.forEach(function (li, i) {
            var km = parseFloat(li.getAttribute('data-km')) || 0;
            var mark = document.createElement('div');
            mark.className = 'ruler-mark' + (i % 2 ? ' is-low' : '') + (i === items.length - 1 ? ' is-end' : '');
            mark.style.setProperty('--x', Math.min(100, (km / max) * 100) + '%');
            var name = document.createElement('strong');
            name.textContent = li.getAttribute('data-town');
            var dist = document.createElement('span');
            dist.textContent = km + ' km';
            mark.appendChild(name);
            mark.appendChild(dist);
            scale.appendChild(mark);
        });
        scale.setAttribute('aria-hidden', 'true');
        ruler.classList.add('is-scaled');
    });

    var drop = document.querySelector('[data-dropzone]');
    if (drop) {
        var input = drop.querySelector('input[type="file"]');
        var list = drop.querySelector('.file-previews');
        var maxFiles = parseInt(drop.getAttribute('data-max-files'), 10) || 5;
        var maxBytes = (parseInt(drop.getAttribute('data-max-mb'), 10) || 5) * 1024 * 1024;
        var allowed = ['image/jpeg', 'image/png', 'image/webp'];

        var render = function () {
            list.textContent = '';
            var files = Array.prototype.slice.call(input.files || []);
            var problems = [];
            if (files.length > maxFiles) {
                problems.push('Kies maximaal ' + maxFiles + " foto's.");
            }
            files.forEach(function (file) {
                var li = document.createElement('li');
                var bad = allowed.indexOf(file.type) === -1 || file.size > maxBytes;
                if (bad) {
                    li.className = 'is-bad';
                    li.textContent = file.name + (file.size > maxBytes ? ' (te groot)' : ' (geen JPG/PNG/WebP)');
                    problems.push(file.name);
                } else {
                    var img = document.createElement('img');
                    img.alt = '';
                    img.src = URL.createObjectURL(file);
                    img.addEventListener('load', function () { URL.revokeObjectURL(img.src); });
                    li.appendChild(img);
                    li.appendChild(document.createTextNode(file.name));
                }
                list.appendChild(li);
            });
            input.setCustomValidity(problems.length ? problems.join(' ') : '');
        };

        input.addEventListener('change', render);
        ['dragenter', 'dragover'].forEach(function (type) {
            drop.addEventListener(type, function (e) { e.preventDefault(); drop.classList.add('is-dragover'); });
        });
        ['dragleave', 'drop'].forEach(function (type) {
            drop.addEventListener(type, function (e) { e.preventDefault(); drop.classList.remove('is-dragover'); });
        });
        drop.addEventListener('drop', function (e) {
            if (e.dataTransfer && e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                render();
            }
        });
    }

    var summary = document.getElementById('error-summary');
    if (summary) {
        summary.focus();
    }

    var started = document.querySelector('form[data-submit-once]');
    if (started) {
        started.addEventListener('submit', function () {
            var btn = started.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.textContent = 'Bezig met versturen…';
            }
        });
    }
})();
