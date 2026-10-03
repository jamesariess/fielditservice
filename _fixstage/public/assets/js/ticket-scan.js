/**
 * Ticket Scan — read a printed Work Order with the camera and pre-fill the
 * New Ticket form (ticket no., company, address, device, serial, task).
 *
 * Flow:  Scan Work Order -> camera / photo -> OCR (Tesseract.js) -> review panel
 *        (every value is editable) -> Fill the ticket form.
 *
 * The OCR engine is fetched on demand from a CDN (same pattern as lucide and
 * SweetAlert2 in includes/layout_header.php) and is cached by the browser
 * afterwards. Anything that cannot be read is left blank on the form so the
 * technician can type or pick it manually — scanning never blocks manual entry.
 */

var TICKET_SCAN_LIBS = [
    'https://cdn.jsdelivr.net/npm/tesseract.js@5.1.1/dist/tesseract.min.js',
    'https://unpkg.com/tesseract.js@5.1.1/dist/tesseract.min.js'
];

// Labels that end the current field when OCR merges two printed columns into
// one line (e.g. "Unit Reported: … Serial/CRTL No.: …").
var TICKET_SCAN_STOP = '(?:contact\\s*person|contact\\s*nos?\\.?|address|company\\s*name|unit\\s*reported|serial|s\\s*\\/\\s*n|crtl|ctrl|request\\b|task\\s*description|action\\s*taken|replacement\\s*part|defective\\s*part|recommendation|status\\s*:|quick\\s*reminder|department|sched(?:uled)?\\s*(?:by|date)|assigned\\s*tech|warranty|charges|custref|part\\s*description|customer\\s*request|instruction)';

var ticketScanLibPromise = null;    // <script> loader for Tesseract.js
var ticketScanWorkerPromise = null; // booting worker
var ticketScanWorker = null;        // reused worker (booting it is slow)
var ticketScanBusy = false;
var ticketScanPhoto = '';           // photo shown in the preview
var ticketScanOcrPhoto = '';        // same photo, resized + contrast boosted
var ticketScanRawText = '';         // raw OCR output (debug panel)
var ticketScanStream = null;        // live camera stream
var ticketScanFacing = 'environment';

// ---------- tiny helpers ----------
function ticketScanEl(id) {
    return document.getElementById(id);
}

function ticketScanIcons() {
    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        try { window.lucide.createIcons(); } catch (e) {}
    }
}

function ticketScanSetValue(id, value) {
    var el = ticketScanEl(id);
    if (el) el.value = value;
}

function ticketScanProgress(percent, message) {
    var bar = ticketScanEl('ticket-scan-progress');
    if (bar) bar.style.width = Math.max(0, Math.min(100, percent)) + '%';
    var text = ticketScanEl('ticket-scan-message');
    if (text && message !== undefined) text.textContent = message;
}

// ---------- open / close / reset ----------
function ticketScanOpen() {
    var modal = ticketScanEl('ticket-scan-modal');
    if (!modal) {
        showToast('The work-order scanner is not available on this page.', 'warning');
        return;
    }
    ticketScanReset();
    openModal('ticket-scan-modal');
    ticketScanShowState('capture');
    ticketScanStartCamera();
}

function ticketScanClose() {
    ticketScanStopCamera();
    var modal = ticketScanEl('ticket-scan-modal');
    if (!modal) return;
    modal.classList.remove('open');
    setTimeout(function() {
        if (modal && !modal.classList.contains('open')) modal.style.display = 'none';
    }, 200);
}

function ticketScanReset() {
    ticketScanPhoto = '';
    ticketScanOcrPhoto = '';
    ticketScanRawText = '';
    ticketScanBusy = false;
    ticketScanProgress(0, 'Starting the camera…');
    var img = ticketScanEl('ticket-scan-photo');
    if (img) img.removeAttribute('src');
    var results = ticketScanEl('ticket-scan-results');
    if (results) results.innerHTML = '';
    var missing = ticketScanEl('ticket-scan-missing');
    if (missing) { missing.style.display = 'none'; missing.innerHTML = ''; }
    var raw = ticketScanEl('ticket-scan-raw');
    if (raw) { raw.style.display = 'none'; raw.textContent = ''; }
    var file = ticketScanEl('ticket-scan-file');
    if (file) file.value = '';
}

// ---------- state switching ----------
function ticketScanShowState(state) {
    var flags = {
        'ticket-scan-preview-wrap': state !== 'capture',
        'ticket-scan-read-actions': state === 'photo',
        'ticket-scan-progress-wrap': state === 'reading',
        'ticket-scan-result-wrap': state === 'result'
    };
    Object.keys(flags).forEach(function(id) {
        var el = ticketScanEl(id);
        if (el) el.style.display = flags[id] ? '' : 'none';
    });
    // Keep the photo visible while reviewing, but smaller so the fields fit.
    var photo = ticketScanEl('ticket-scan-photo');
    if (photo) photo.style.maxHeight = state === 'result' ? '20vh' : '34vh';
    if (state === 'capture') ticketScanCameraUi(ticketScanStream ? 'live' : 'starting');
    else {
        var live = ticketScanEl('ticket-scan-live');
        var fallback = ticketScanEl('ticket-scan-fallback');
        if (live) live.style.display = 'none';
        if (fallback) fallback.style.display = 'none';
    }
    ticketScanIcons();
}

function ticketScanCameraUi(stage, message) {
    var live = ticketScanEl('ticket-scan-live');
    var fallback = ticketScanEl('ticket-scan-fallback');
    var hint = ticketScanEl('ticket-scan-camera-hint');
    if (live) live.style.display = stage === 'live' ? '' : 'none';
    if (fallback) fallback.style.display = stage === 'live' ? 'none' : '';
    if (hint) hint.textContent = message || (stage === 'starting' ? 'Starting the camera…' : '');
}

// ---------- camera ----------
function ticketScanStartCamera() {
    var video = ticketScanEl('ticket-scan-video');
    if (!video) return;
    if (!navigator.mediaDevices || typeof navigator.mediaDevices.getUserMedia !== 'function') {
        ticketScanCameraUi('blocked', 'This browser cannot open the camera here — use the button below to take or choose a photo.');
        return;
    }
    ticketScanCameraUi('starting');
    navigator.mediaDevices.getUserMedia({
        video: { facingMode: { ideal: ticketScanFacing }, width: { ideal: 1920 }, height: { ideal: 1080 } },
        audio: false
    }).then(function(stream) {
        ticketScanStream = stream;
        video.srcObject = stream;
        var play = video.play();
        if (play && typeof play.catch === 'function') play.catch(function() {});
        ticketScanCameraUi('live');
    }).catch(function() {
        ticketScanCameraUi('blocked', 'Camera permission was denied — use the button below to take or choose a photo of the work order.');
    });
}

function ticketScanStopCamera() {
    if (ticketScanStream) {
        try {
            ticketScanStream.getTracks().forEach(function(track) { track.stop(); });
        } catch (e) {}
        ticketScanStream = null;
    }
    var video = ticketScanEl('ticket-scan-video');
    if (video) {
        try { video.pause(); } catch (e) {}
        video.srcObject = null;
    }
}

function ticketScanFlipCamera() {
    ticketScanFacing = ticketScanFacing === 'environment' ? 'user' : 'environment';
    ticketScanStopCamera();
    ticketScanStartCamera();
}

function ticketScanCapture() {
    var video = ticketScanEl('ticket-scan-video');
    if (!video || !video.videoWidth) {
        showToast('The camera is still starting — try again in a second.', 'warning');
        return;
    }
    var canvas = document.createElement('canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
    ticketScanUsePhoto(canvas.toDataURL('image/jpeg', 0.92));
}

function ticketScanPickFile() {
    var file = ticketScanEl('ticket-scan-file');
    if (file) file.click();
}

function ticketScanFileChosen(input) {
    if (!input || !input.files || !input.files[0]) return;
    var reader = new FileReader();
    reader.onload = function() {
        ticketScanUsePhoto(String(reader.result || ''));
        input.value = '';
    };
    reader.onerror = function() {
        showToast('That photo could not be opened. Please try another one.', 'error');
        input.value = '';
    };
    reader.readAsDataURL(input.files[0]);
}

function ticketScanUsePhoto(dataUrl) {
    if (!dataUrl) return;
    ticketScanStopCamera();
    ticketScanPhoto = dataUrl;
    ticketScanRawText = '';
    var img = ticketScanEl('ticket-scan-photo');
    if (img) img.src = dataUrl;
    var raw = ticketScanEl('ticket-scan-raw');
    if (raw) { raw.style.display = 'none'; raw.textContent = ''; }
    var results = ticketScanEl('ticket-scan-results');
    if (results) results.innerHTML = '';
    ticketScanShowState('photo');
}

function ticketScanRetake() {
    ticketScanStopCamera();
    ticketScanReset();
    ticketScanShowState('capture');
    ticketScanStartCamera();
}

// ---------- OCR engine (loaded on demand) ----------
function ticketScanLoadLib() {
    if (window.Tesseract) return Promise.resolve(window.Tesseract);
    if (ticketScanLibPromise) return ticketScanLibPromise;
    ticketScanLibPromise = new Promise(function(resolve, reject) {
        var index = 0;
        function attempt() {
            if (index >= TICKET_SCAN_LIBS.length) { reject(new Error('Scanner unavailable')); return; }
            var script = document.createElement('script');
            script.src = TICKET_SCAN_LIBS[index++];
            script.async = true;
            script.onload = function() {
                if (window.Tesseract) resolve(window.Tesseract);
                else attempt();
            };
            script.onerror = function() { attempt(); };
            document.head.appendChild(script);
        }
        attempt();
    });
    return ticketScanLibPromise;
}

function ticketScanGetWorker() {
    if (ticketScanWorker) return Promise.resolve(ticketScanWorker);
    if (ticketScanWorkerPromise) return ticketScanWorkerPromise;
    ticketScanWorkerPromise = ticketScanLoadLib().then(function(Tesseract) {
        return Tesseract.createWorker('eng', 1, {
            logger: function(m) {
                if (!m || !m.status) return;
                if (m.status === 'loading tesseract core' && m.progress < 1) {
                    ticketScanProgress(4 + m.progress * 16, 'Preparing the scanner (first time only)…');
                } else if (m.status.indexOf('loading language') === 0) {
                    ticketScanProgress(20 + m.progress * 14, 'Loading English text data (first time only)…');
                } else if (m.status === 'initializing api') {
                    ticketScanProgress(36, 'Warming up the reader…');
                } else if (m.status === 'recognizing text') {
                    ticketScanProgress(42 + m.progress * 54, 'Reading the work order… ' + Math.round(m.progress * 100) + '%');
                }
            }
        });
    }).then(function(worker) {
        return worker.setParameters({ preserve_interword_spaces: '1' }).then(function() {
            ticketScanWorker = worker;
            return worker;
        });
    }).catch(function(err) {
        ticketScanWorkerPromise = null;
        throw err;
    });
    return ticketScanWorkerPromise;
}

// Phone photos are big and often low on contrast: shrink them for speed and
// stretch the levels so the printed lines come out black on white.
function ticketScanPreparePhoto(dataUrl) {
    return new Promise(function(resolve) {
        var img = new Image();
        img.onload = function() {
            try {
                var maxSide = 2200;
                var scale = Math.min(1, maxSide / Math.max(img.width, img.height));
                var w = Math.max(1, Math.round(img.width * scale));
                var h = Math.max(1, Math.round(img.height * scale));
                var canvas = document.createElement('canvas');
                canvas.width = w;
                canvas.height = h;
                var ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, w, h);
                var imageData = ctx.getImageData(0, 0, w, h);
                var px = imageData.data;
                for (var i = 0; i < px.length; i += 4) {
                    var gray = 0.299 * px[i] + 0.587 * px[i + 1] + 0.114 * px[i + 2];
                    var v = (gray - 62) * (255 / (232 - 62));
                    v = v < 0 ? 0 : (v > 255 ? 255 : v);
                    px[i] = px[i + 1] = px[i + 2] = v;
                }
                ctx.putImageData(imageData, 0, 0);
                resolve(canvas.toDataURL('image/jpeg', 0.92));
            } catch (e) {
                resolve(dataUrl);   // canvas blocked -> let OCR use the original photo
            }
        };
        img.onerror = function() { resolve(dataUrl); };
        img.src = dataUrl;
    });
}

// The first pass uses the default page layout. When it cannot find the SD
// number — the one field nothing else can replace, and the field that gets lost
// first on a photo that is tilted or cropped at the edge — a second pass runs in
// single-column mode (which tolerates that) and the two texts are merged. The
// first result is never thrown away, so a failed second pass costs nothing.
function ticketScanRecognize(worker, photo) {
    return worker.recognize(photo).then(function(result) {
        var text = (result && result.data && result.data.text) || '';
        if (ticketScanSdNumbers(text).length) return result;
        ticketScanProgress(60, 'Having a second look at the top of the work order…');
        return worker.setParameters({ tessedit_pageseg_mode: '6' })
            .then(function() { return worker.recognize(photo); })
            .then(function(second) {
                var more = (second && second.data && second.data.text) || '';
                if (!more) return result;
                return { data: { text: text + '\n' + more } };
            })
            .catch(function() { return result; });
    });
}

function ticketScanRead() {
    if (ticketScanBusy) return;
    if (!ticketScanPhoto) { showToast('Take or choose a photo of the work order first.', 'warning'); return; }
    ticketScanBusy = true;
    ticketScanShowState('reading');
    ticketScanProgress(2, 'Preparing the photo…');
    ticketScanPreparePhoto(ticketScanPhoto)
        .then(function(prepared) {
            ticketScanOcrPhoto = prepared;
            return ticketScanGetWorker();
        })
        .then(function(worker) {
            ticketScanProgress(40, 'Reading the work order…');
            return ticketScanRecognize(worker, ticketScanOcrPhoto);
        })
        .then(function(result) {
            ticketScanBusy = false;
            ticketScanRawText = (result && result.data && result.data.text) || '';
            var parsed = ticketScanParse(ticketScanRawText);
            ticketScanRenderResults(parsed);
            ticketScanShowState('result');
            if (parsed.read_count === 0) {
                showToast('Nothing could be read from that photo — move closer, use better light, or type the details in manually.', 'warning');
            } else {
                showToast('Work order read. Check the detected fields, then apply them to the form.', 'info');
            }
        })
        .catch(function(err) {
            ticketScanBusy = false;
            ticketScanShowState('photo');
            if (err && err.message === 'Scanner unavailable') {
                showToast('The scanner needs an internet connection the first time it runs. You can still type the details in manually.', 'error');
            } else {
                showToast('Could not read that photo — try again, or type the details in manually.', 'error');
            }
        });
}

// ==================== Work-order text parser ====================
// OCR output is noisy: printed columns merge into one line, handwriting turns
// into junk, faint dots are read as '·'. The parser works label by label and
// cuts each value at the next known label, so a wrong read never spills into
// the neighbouring field.

function ticketScanCleanLines(rawText) {
    return String(rawText || '')
        .replace(/\r/g, '\n')
        .replace(/\u00A0/g, ' ')
        .split('\n')
        .map(function(line) {
            return line
                .replace(/\s{2,}/g, ' ')
                .replace(/^[\s|¦:;•·._-]+/, '')
                .replace(/[\s|¦]+$/, '')
                .trim();
        })
        .filter(function(line) { return line.length > 1; });
}

function ticketScanIsStop(line) {
    return new RegExp('^' + TICKET_SCAN_STOP, 'i').test(String(line || '').replace(/^[\s|¦]+/, ''));
}

// Everything from the label up to the next label on the same line.
function ticketScanCut(value) {
    var v = String(value || '');
    var match = v.match(new RegExp('[\\s|¦,;]+' + TICKET_SCAN_STOP, 'i'));
    if (match) v = v.slice(0, match.index);
    // Trailing commas are kept: an address often wraps onto the next line and
    // the comma belongs to the printed text.
    return v
        .replace(/\s{2,}/g, ' ')
        .replace(/^[\s:;•·.,\-_]+/, '')
        .replace(/[\s:;•·\-_]+$/, '')
        .trim();
}

function ticketScanTidy(value, keepTail) {
    var v = String(value || '').replace(/\s{2,}/g, ' ').trim();
    v = v.replace(/^[|¦,;:._\-•·\s]+/, '').replace(/[|¦,;:_\-•·\s]+$/, '').trim();
    // Right-hand column noise often leaves a single letter at the end.
    // Circled digits and other glyph junk OCR leaves behind the value
    // ("Banco De Oro ②").
    v = v.replace(/[\u2460-\u24FF\u2600-\u27BF\u00A9\u00AE\u2122\uFE0F\u20E3\uFFFD]/g, ' ')
        .replace(/[\u200B-\u200F\u202A-\u202E]/g, '')
        .replace(/\s{2,}/g, ' ')
        .trim();
    v = ticketScanDropLabelTail(v);
    if (!keepTail) v = v.replace(/\s+[A-Za-z]$/, '');
    return v.trim();
}

// Lines that follow a label (a value can be printed on the next line, or the
// label line itself can carry it). Stops at the next known label.
function ticketScanBlock(lines, labelRe, maxLines) {
    var limit = maxLines || 1;
    var collected = [];
    for (var i = 0; i < lines.length; i++) {
        var match = lines[i].match(labelRe);
        if (!match) continue;
        var rest = ticketScanCut(lines[i].slice(match.index + match[0].length));
        if (rest) collected.push(rest);
        for (var j = i + 1; j < lines.length && collected.length < limit; j++) {
            if (ticketScanIsStop(lines[j])) break;
            var next = ticketScanCut(lines[j]);
            if (!next) break;
            collected.push(next);
        }
        break;
    }
    return collected.filter(function(value) { return value && value.length > 0; });
}

function ticketScanAfter(lines, labelRe, opts) {
    opts = opts || {};
    var parts = ticketScanBlock(lines, labelRe, opts.maxLines || 1);
    return ticketScanTidy(parts.join(' '), opts.keepTail);
}

function ticketScanEscapeRe(value) {
    return String(value || '').replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

function ticketScanSerial(value) {
    var s = String(value || '').toUpperCase().replace(/[^A-Z0-9\-\/]/g, '');
    if (s.length < 4 || s.length > 26) return '';
    if (!/[A-Z]/.test(s) && !/\d{6,}/.test(s)) return '';
    return s;
}

function ticketScanEquipmentManufacturers() {
    var seen = {}, out = [];
    (window.ttEqData || []).forEach(function(row) {
        var name = String(row.manufacturer || '').trim();
        if (!name) return;
        var key = name.toLowerCase();
        if (seen[key]) return;
        seen[key] = true;
        out.push(name);
    });
    // Longest first so the printed brand always wins over a short alias.
    return out.sort(function(a, b) { return b.length - a.length; });
}

function ticketScanKnownManufacturer(unitText) {
    var text = String(unitText || '').toLowerCase();
    if (!text) return '';
    var names = ticketScanEquipmentManufacturers();
    for (var i = 0; i < names.length; i++) {
        if (text.indexOf(names[i].toLowerCase()) !== -1) return names[i];
    }
    var first = String(unitText || '').trim().split(/\s+/)[0] || '';
    if (/^[A-Za-z][A-Za-z\-]{2,}$/.test(first) && !/\d/.test(first)) return first;
    return '';
}

function ticketScanDeviceTypes() {
    var out = [];
    (window.ttEqData || []).forEach(function(row) {
        var type = String(row.device_type || '').trim();
        if (type && out.indexOf(type) === -1) out.push(type);
    });
    return out;
}

function ticketScanGuessDeviceType(unitText) {
    var text = String(unitText || '').toLowerCase();
    if (!text) return '';
    var types = ticketScanDeviceTypes();
    for (var i = 0; i < types.length; i++) {
        var t = types[i].toLowerCase();
        if (t && (text.indexOf(t) !== -1 || (t.length > 4 && text.indexOf(t.replace(/s$/, '')) !== -1))) return types[i];
    }
    var keywords = [
        ['laptop', 'Laptop'], ['notebook', 'Laptop'], ['ultrabook', 'Laptop'], ['gen ', 'Laptop'],
        ['thinkpad', 'Laptop'], ['ideapad', 'Laptop'], ['thinkbook', 'Laptop'], ['aspire', 'Laptop'],
        ['macbook', 'Laptop'], ['latitude', 'Laptop'], ['elitebook', 'Laptop'], ['probook', 'Laptop'],
        ['pavilion', 'Laptop'], ['vivobook', 'Laptop'], ['zenbook', 'Laptop'], ['inspiron', 'Laptop'],
        ['desktop', 'Desktop'], ['tower', 'Desktop'], ['all-in-one', 'Desktop'], ['aio', 'Desktop'],
        ['optiplex', 'Desktop'], ['thinkcentre', 'Desktop'], ['prodesk', 'Desktop'], ['elitedesk', 'Desktop'],
        ['printer', 'Printer'], ['laserjet', 'Printer'], ['deskjet', 'Printer'], ['ecotank', 'Printer'],
        ['scanner', 'Scanner'], ['monitor', 'Monitor'], ['display', 'Monitor'], ['projector', 'Projector'],
        ['lfd', 'Monitor'], ['led panel', 'Monitor'], ['signage', 'Monitor'],
        ['ups', 'UPS'], ['avr', 'UPS'], ['server', 'Server'], ['poweredge', 'Server'], ['proliant', 'Server'],
        ['cctv', 'CCTV'], ['ip cam', 'CCTV'], ['nvr', 'CCTV'], ['dvr', 'CCTV'],
        ['router', 'Network'], ['switch', 'Network'], ['access point', 'Network'], ['firewall', 'Network'],
        ['keyboard', 'Keyboard'], ['mouse', 'Mouse'], ['headset', 'Headset'], ['tablet', 'Tablet']
    ];
    for (var k = 0; k < keywords.length; k++) {
        if (text.indexOf(keywords[k][0]) === -1) continue;
        for (var j = 0; j < types.length; j++) {
            if (types[j].toLowerCase().indexOf(keywords[k][1].toLowerCase()) !== -1) return types[j];
        }
        return keywords[k][1];
    }
    return '';
}

// A label can also be left half-read at the end of a merged line ("… Plaza
// Drive, Customer" when the right column starts "Customer Request / …").
var TICKET_SCAN_LABEL_WORDS = /^(?:customer|contact|unit|request|task|action|serial|part|parts|reported|address|charges|custref|department|status|assigned|warranty|note|recommendation|description|problem|person|ref|tech|no|nos|sched|scheduled)$/i;

// The ticket number is the number printed next to SD (SD264613131 -> 264613131,
// the form keeps "SD" as a fixed prefix). OCR slips in those digits (O for 0,
// I/l for 1, S for 5) are mapped back and the topmost SD on the sheet wins — it
// is always the first line of the page.
var TICKET_SCAN_SD_RE = /\b[SD5][DO0][\s.:\-#]{0,3}([0-9OoIlQZBSGT|]{5,18})/gi;
// Same, for a photo where OCR spaced the digits out ("SD 264 613 131"): the
// strict pattern above is tried first so the loose one can never make it worse.
var TICKET_SCAN_SD_SPACED_RE = /\b[SD5][DO0][\s.:\-#]{0,3}([0-9OoIlQZBSGT|][0-9OoIlQZBSGT|\s]{4,20})/gi;
var TICKET_SCAN_OCR_DIGITS = { O: '0', Q: '0', D: '0', I: '1', L: '1', '|': '1', S: '5', B: '8', Z: '2', G: '6', T: '7' };

function ticketScanSdDigits(raw, nextChar) {
    var value = String(raw || '');
    // OCR glues the next word's first letter onto the number ("1041036Request"
    // or "264613131 Sched"): a trailing letter followed by a letter is that
    // word, not a digit.
    if (/[A-Za-z]/.test(String(nextChar || '')) && /[A-Za-z|]\s*$/.test(value)) {
        value = value.replace(/[A-Za-z|]\s*$/, '');
    }
    value = value.replace(/\s+/g, '');
    if ((value.match(/\d/g) || []).length < 5) return '';   // not a printed number
    var digits = '';
    for (var i = 0; i < value.length; i++) {
        var ch = value.charAt(i).toUpperCase();
        if (/\d/.test(ch)) digits += ch;
        else if (TICKET_SCAN_OCR_DIGITS[ch]) digits += TICKET_SCAN_OCR_DIGITS[ch];
        else return '';
    }
    if (digits.length < 6 || digits.length > 12) return '';
    return digits;
}

function ticketScanSdMatches(text, pattern) {
    var out = [], match;
    var source = String(text || '');
    pattern.lastIndex = 0;
    while ((match = pattern.exec(source)) !== null) {
        var digits = ticketScanSdDigits(match[1], source.charAt(match.index + match[0].length));
        if (!digits || out.indexOf(digits) !== -1) continue;
        out.push(digits);
    }
    return out;
}

function ticketScanSdNumbers(text) {
    var strict = ticketScanSdMatches(text, TICKET_SCAN_SD_RE);
    if (strict.length) return strict;
    return ticketScanSdMatches(text, TICKET_SCAN_SD_SPACED_RE);
}

// When the "SD" itself is unreadable the number is still printed on the sheet,
// so the long digit runs the scan did see are offered next to the field.
function ticketScanNumberCandidates(text, exclude) {
    var out = [];
    var tokens = String(text || '').split(/[^0-9]+/);
    for (var i = 0; i < tokens.length; i++) {
        var token = tokens[i];
        if (token.length < 6 || token.length > 12) continue;
        if (exclude.indexOf(token) !== -1) continue;
        if (/^0{2,}/.test(token)) continue;             // 00:00 timestamps
        if (/^09\d{9}$/.test(token)) continue;          // mobile number (contact column)
        if (/^(?:19|20)\d{6}$/.test(token)) continue;   // 20260926 date
        if (out.indexOf(token) === -1) out.push(token);
        if (out.length >= 3) break;
    }
    return out;
}

function ticketScanDropLabelTail(value) {
    var words = String(value || '').trim().split(/\s+/);
    while (words.length > 1) {
        var last = words[words.length - 1].replace(/[^A-Za-z]/g, '');
        if (!last || last.length > 12 || !TICKET_SCAN_LABEL_WORDS.test(last)) break;
        words.pop();
    }
    return words.join(' ').trim();
}

// The right-hand column bleeds the contact row into the address
// ("T 09178502783 …"): drop the phone numbers and their stray marker letters.
function ticketScanStripPhones(value) {
    return String(value || '')
        .replace(/(^|\s)[T@©®]\s*(?=\d)/g, ' ')
        .replace(/\+?\b\d{2,4}[\s\-.]?\d{3}[\s\-.]?\d{4}\b/g, ' ')
        .replace(/\b(?:tel|telefax|fax|mobile|landline|contact\s*nos?)\b\.?\s*[:.]?/gi, ' ')
        .replace(/\s{2,}/g, ' ')
        .trim();
}

// OCR noise lines ("SESW [ls ws 1 PP) RA) 0 Rl 1") are not address text.
function ticketScanLooksLikeJunk(line) {
    var s = String(line || '').trim();
    if (!s) return true;
    if (/[[\]{}<>~^_=]/.test(s)) return true;
    if (/[()]/.test(s) && !/\b(?:st|ave|cor|bldg|unit|rm|floor)\b/i.test(s)) return true;
    return (s.split(/\s+/).filter(function(w) { return /[A-Za-z]{2,}/.test(w); }).length) < 1;
}

// Floors are misread as "GIF" / "G1F" / "G/F." — put the slash back.
function ticketScanTidyAddress(value) {
    var v = ticketScanStripPhones(value);
    v = v.replace(/\b([GB])\s*(?:I|1|l|\||\/)\s*F(\.)?/gi, function(m, floor, dot) {
        return floor.toUpperCase() + '/F' + (dot ? ',' : '');
    });
    v = v.replace(/\b([1-9])\s*(?:I|1|l|\||\/)\s*F\b/gi, function(m, floor) { return floor + '/F'; });
    return ticketScanTidy(v, true);
}

// One line of the address block: cut at the next printed label, but keep the
// comma that separated the left column from the right one.
function ticketScanAddressPiece(raw) {
    var text = String(raw || '');
    var match = text.match(new RegExp('[\\s|¦,;]+' + TICKET_SCAN_STOP, 'i'));
    if (match) {
        var sep = match[0].replace(/[\s|¦]/g, '');
        text = text.slice(0, match.index) + (sep.indexOf(',') !== -1 ? ',' : '');
    }
    return text.replace(/\s{2,}/g, ' ').replace(/^[\s:;•·|¦]+/, '').trim();
}

// The address is printed under its label (one or two lines) and the right-hand
// column leaks into the same OCR line, so only text that still looks like an
// address is collected.
function ticketScanAddress(lines) {
    var idx = -1, first = '';
    for (var i = 0; i < lines.length; i++) {
        var match = String(lines[i] || '').match(/^address\s*[:\-]?\s*/i);
        if (!match) continue;
        idx = i;
        first = ticketScanAddressPiece(lines[i].slice(match[0].length));
        break;
    }
    if (idx === -1) return '';

    var parts = [];
    var open = true;
    if (first) { parts.push(first); open = /[,;]$/.test(first); }
    for (var j = idx + 1; j < lines.length && parts.length < 3; j++) {
        var raw = String(lines[j] || '').trim();
        if (!raw || ticketScanIsStop(raw) || ticketScanLooksLikeJunk(raw)) break;
        if (parts.length >= 2 && !open) break;   // the line above looked finished
        var piece = ticketScanAddressPiece(raw);
        if (!piece || !/[A-Za-z]{2,}/.test(piece)) break;
        parts.push(piece);
        open = /[,;]$/.test(piece);
    }
    return ticketScanTidyAddress(parts.join(' '));
}

// ==================== Work-order field extraction ====================
function ticketScanParse(rawText) {
    var lines = ticketScanCleanLines(rawText);
    var text = lines.join('\n');
    var out = {
        ticket_no: '', serial: '', company: '', address: '', unit: '',
        manufacturer: '', model: '', device_type: '', task: '', problem: '',
        customer: '', note: '', read_count: 0, raw: text
    };

    // Work Order No. -> the ticket number ("SD1040842" -> "1040842", the form
    // keeps "SD" as a fixed prefix).
    // The ticket number is the SD number printed at the top of the work order
    // ("Order No.: SD1041040" / "Work Order No.: SD1041036"), so the topmost SD
    // match wins. The label matches below are only a fallback for sheets that
    // carry no SD number at all.
    var sdNumbers = ticketScanSdNumbers(text);
    if (sdNumbers.length) out.ticket_no = sdNumbers[0];
    if (sdNumbers.length > 1) out.ticket_alt = sdNumbers[1];
    if (!out.ticket_no) out.ticket_candidates = ticketScanNumberCandidates(text, sdNumbers);
    var wo = text.match(/(?:work\s*order|w\.?\s*o\.?|ticket|job)\s*(?:no\.?|number|#)?\s*[:\-]?\s*([A-Za-z]{0,4}[\s\-]?\d{3,12})\b/i);
    if (wo && !out.ticket_no) {
        var digits = wo[1].match(/\d{3,12}/);
        out.ticket_no = digits ? digits[0] : '';
    }
    if (!out.ticket_no) {
        var prefixed = text.match(/\b(?:SD|WO|TK)\s*[-\s]?(\d{4,12})\b/i);
        if (prefixed) out.ticket_no = prefixed[1];
    }

    // Serial / CRTL No.
    var serialMatch = text.match(/(?:serial|s\s*\/\s*n|crtl|ctrl)\s*(?:no\.?|number|#)?\s*[:\-]?\s*([A-Za-z0-9][A-Za-z0-9\-\/\. ]{3,26})/i);
    if (serialMatch) out.serial = ticketScanSerial(serialMatch[1]);

    // Company name
    out.company = ticketScanAfter(lines, /company\s*(?:name)?\s*[:\-]/i, { maxLines: 2 }).split('|')[0].trim();

    // Address (often printed on the line(s) under the label)
    out.address = ticketScanAddress(lines);

    // Unit Reported -> device / model (+ a device-type guess)
    out.unit = ticketScanAfter(lines, /unit\s*reported\s*[:\-]/i, { maxLines: 1, keepTail: true });
    out.manufacturer = ticketScanKnownManufacturer(out.unit);
    var modelText = out.unit;
    if (out.manufacturer) {
        modelText = out.unit.replace(new RegExp('^\\s*' + ticketScanEscapeRe(out.manufacturer) + '\\s*', 'i'), '');
    }
    out.model = ticketScanTidy(modelText, true).slice(0, 80);
    out.device_type = ticketScanGuessDeviceType(out.unit);

    // Task Description / Reported Problem (label + the printed lines under it)
    var taskLines = ticketScanBlock(lines, /task\s*description\s*[^:\n]{0,40}[:\-]/i, 3);
    if (taskLines.length) {
        out.task = ticketScanTidy(taskLines[0], true).slice(0, 90);
        out.problem = taskLines.join(' ').replace(/\s{2,}/g, ' ').slice(0, 480).trim();
    }

    // "Unit Reported: Samsung ED40C" names no device by itself, but the task
    // text usually does ("For dismantling of LFD") — use it as a second chance.
    if (!out.device_type) {
        out.device_type = ticketScanGuessDeviceType(out.unit + ' ' + out.task + ' ' + out.problem);
    }

    // Contact person (left column of the contact row)
    out.customer = ticketScanAfter(lines, /contact\s*person\s*[:\-]/i, { maxLines: 1 }).split('|')[0].trim().slice(0, 60);

    // Reference note: request type + contact numbers (handy inside the ticket)
    var request = ticketScanAfter(lines, /request\s*[:\-]/i, { maxLines: 1, keepTail: true });
    var contactNo = ticketScanAfter(lines, /contact\s*nos?\.?\s*[:\-]/i, { maxLines: 1, keepTail: true });
    var bits = [];
    if (request) bits.push('Request: ' + request);
    if (contactNo) bits.push('Contact No.: ' + contactNo);
    out.note = bits.join(' · ').slice(0, 200);

    // Serial fallback: a lone mixed letters+digits token. Never the work-order
    // number and never the printed model/part number.
    if (!out.serial) {
        var tokens = text.split(/[\s|]+/);
        for (var t = 0; t < tokens.length; t++) {
            var token = tokens[t].replace(/[^A-Za-z0-9\-\/]/g, '');
            if (token.length < 6 || token.length > 18) continue;
            if (!/[A-Za-z]/.test(token) || !/\d/.test(token)) continue;
            if (/^[A-Za-z]{0,3}\d{4,}$/.test(token)) continue;
            var upper = token.toUpperCase();
            if (out.ticket_no && upper.indexOf(out.ticket_no) !== -1) continue;
            if (out.unit && out.unit.toUpperCase().indexOf(upper) !== -1) continue;
            out.serial = upper;
            break;
        }
    }
    if (out.serial && out.ticket_no && out.serial.replace(/[^0-9]/g, '') === out.ticket_no) out.serial = '';

    var count = 0;
    ['ticket_no', 'serial', 'company', 'address', 'model', 'task', 'problem', 'customer'].forEach(function(key) {
        if (String(out[key] || '').trim()) count++;
    });
    out.read_count = count;
    return out;
}

// ---------- equipment lookup (serial / model read off the work order) ----------
// OCR swaps 0/O and 1/I/L almost every time it reads a stamped serial, so a few
// variants are compared against the equipment list before giving up.
function ticketScanSerialKeys(value) {
    var base = String(value || '').toUpperCase().replace(/[^A-Z0-9]/g, '');
    var keys = [];
    function add(key) {
        if (key && keys.indexOf(key) === -1) keys.push(key);
    }
    add(base);
    add(base.replace(/O/g, '0'));
    add(base.replace(/[IL]/g, '1'));
    add(base.replace(/O/g, '0').replace(/[IL]/g, '1').replace(/S/g, '5').replace(/B/g, '8'));
    add(base.replace(/0/g, 'O').replace(/1/g, 'I'));
    return keys;
}

function ticketScanMatchEquipment(serial, deviceText) {
    var rows = window.ttEqData || [];
    if (!rows.length) return null;
    var keys = ticketScanSerialKeys(serial);
    if (keys.length) {
        for (var i = 0; i < rows.length; i++) {
            var rowKey = String(rows[i].serial_number || '').toUpperCase().replace(/[^A-Z0-9]/g, '');
            if (rowKey && keys.indexOf(rowKey) !== -1) return rows[i];
        }
    }
    var wanted = String(deviceText || '').toLowerCase().replace(/[^a-z0-9]+/g, ' ').trim();
    if (wanted.length >= 4) {
        var best = null, bestScore = 0;
        for (var j = 0; j < rows.length; j++) {
            var model = String(rows[j].model_name || '').toLowerCase().replace(/[^a-z0-9]+/g, ' ').trim();
            if (!model || model.length < 3) continue;
            var score = 0;
            if (wanted.indexOf(model) !== -1) score = model.length;
            else if (model.indexOf(wanted) !== -1) score = wanted.length;
            if (score >= 4 && score > bestScore) { best = rows[j]; bestScore = score; }
        }
        if (best) return best;
    }
    return null;
}

// ==================== Review panel ====================
function ticketScanOptionValues(selectId) {
    var sel = ticketScanEl(selectId);
    var out = [];
    if (!sel) return out;
    Array.prototype.forEach.call(sel.options, function(opt) {
        var value = String(opt.value || '').trim();
        if (value && value !== '__OTHER__' && out.indexOf(value) === -1) out.push(value);
    });
    return out;
}

function ticketScanModelNames() {
    var out = [];
    (window.ttEqData || []).forEach(function(row) {
        var name = String(row.model_name || '').trim();
        if (name && out.indexOf(name) === -1) out.push(name);
    });
    return out.slice(0, 200);
}

function ticketScanDatalist(id, values) {
    return '<datalist id="' + id + '">' + values.map(function(value) {
        return '<option value="' + escHtml(value) + '"></option>';
    }).join('') + '</datalist>';
}

// One editable row: what was read, plus a badge telling the technician whether
// the scanner found it. Empty rows stay highlighted so they are obvious.
function ticketScanRow(label, field, value, opts) {
    opts = opts || {};
    var text = String(value || '').trim();
    var filled = text !== '';
    var badge = filled
        ? '<span style="font-size:10px;font-weight:700;color:#15803d;background:#dcfce7;border-radius:99px;padding:2px 8px;white-space:nowrap;">detected</span>'
        : '<span style="font-size:10px;font-weight:700;color:#b45309;background:#fef3c7;border-radius:99px;padding:2px 8px;white-space:nowrap;">not read — fill in</span>';
    var style = 'width:100%;padding:9px 12px;border:1px solid ' + (filled ? '#d1d5db' : '#f59e0b')
        + ';border-radius:8px;font-size:13px;' + (filled ? '' : 'background:#fffbeb;');
    var input;
    if (opts.options) {
        input = '<select data-scan-field="' + field + '" style="' + style + '">'
            + '<option value="">— not set —</option>'
            + opts.options.map(function(option) {
                return '<option value="' + escHtml(option) + '"' + (option === text ? ' selected' : '') + '>' + escHtml(option) + '</option>';
            }).join('')
            + '</select>';
    } else if (opts.multiline) {
        input = '<textarea data-scan-field="' + field + '" rows="2" style="' + style + 'resize:vertical;">' + escHtml(text) + '</textarea>';
    } else {
        input = '<input data-scan-field="' + field + '" type="text" value="' + escHtml(text) + '"'
            + (opts.list ? ' list="' + opts.list + '"' : '') + ' style="' + style + '">';
    }
    return '<div>'
        + '<div style="display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:4px;">'
        + '<label style="font-size:11.5px;font-weight:700;color:#374151;">' + escHtml(label) + (opts.required ? ' <span style="color:#dc2626;">*</span>' : '') + '</label>'
        + badge
        + '</div>' + input
        + (opts.hintHtml
            ? '<div style="font-size:10.5px;color:#94a3b8;margin-top:4px;">' + opts.hintHtml + '</div>'
            : (opts.hint ? '<div style="font-size:10.5px;color:#94a3b8;margin-top:4px;">' + escHtml(opts.hint) + '</div>' : ''))
        + '</div>';
}

function ticketScanRenderResults(parsed) {
    var wrap = ticketScanEl('ticket-scan-results');
    if (!wrap) return;
    var data = parsed || {};
    var equipment = ticketScanMatchEquipment(data.serial, data.model || data.unit || '');
    var matchedLabel = equipment ? ((equipment.manufacturer || '') + ' ' + (equipment.model_name || '')).trim() : '';
    var deviceTypes = ticketScanDeviceTypes();
    if (data.device_type && deviceTypes.indexOf(data.device_type) === -1) deviceTypes.unshift(data.device_type);
    var html = '';
    // When the sheet prints two SD numbers (an "Order No." line above the
    // "Work Order No." row) the topmost one is filled in, and the other is
    // offered here so it can be swapped with one tap.
    var ticketHint = 'SD is added automatically.';
    var ticketHintHtml = '';
    if (data.ticket_alt && String(data.ticket_alt) !== String(data.ticket_no)) {
        ticketHintHtml = 'SD is added automatically. Another SD number on this page: '
            + '<button type="button" data-scan-use="' + escHtml(data.ticket_alt) + '" style="font:inherit;font-weight:700;color:#2563eb;background:#eff6ff;border:1px solid #bfdbfe;border-radius:99px;padding:1px 8px;cursor:pointer;">SD'
            + escHtml(data.ticket_alt) + '</button> — tap it if that is the ticket no.';
    } else if (!data.ticket_no) {
        // Nothing matched: say what the scan actually read, so it is obvious
        // whether the sheet or the photo is at fault, and offer the numbers it
        // did find (one tap fills the field).
        var chips = (data.ticket_candidates || []).map(function(candidate) {
            return '<button type="button" data-scan-use="' + escHtml(candidate) + '" style="font:inherit;font-weight:700;color:#2563eb;background:#eff6ff;border:1px solid #bfdbfe;border-radius:99px;padding:1px 8px;cursor:pointer;">SD' + escHtml(candidate) + '</button>';
        });
        var hintBits = [];
        if (chips.length) hintBits.push('Numbers read on this sheet: ' + chips.join(' '));
        var firstLines = String(data.raw || '').split('\n').slice(0, 2).join(' / ').slice(0, 140);
        if (firstLines) hintBits.push('What the scan read at the top: “' + escHtml(firstLines) + '” — if no SD number is in that text, retake the photo straight and close-up.');
        ticketHintHtml = hintBits.join('<br>');
    }
    html += ticketScanRow('Ticket No. (Work Order No.)', 'ticket_no', data.ticket_no, { required: true, hint: ticketHint, hintHtml: ticketHintHtml });
    html += ticketScanRow('Company Name', 'company', data.company, { required: true, list: 'ticket-scan-company-list' });
    html += ticketScanRow('Device Type', 'device_type', data.device_type, { required: true, options: deviceTypes });
    html += ticketScanRow('Device / Model', 'model', data.model, {
        list: 'ticket-scan-model-list',
        hint: matchedLabel
            ? 'Matched equipment on file: ' + matchedLabel + (equipment.serial_number ? ' · SN ' + equipment.serial_number : '')
            : 'Taken from "Unit Reported" — edit it if the scan misread it.'
    });
    html += ticketScanRow('Serial Number (Serial/CRTL No.)', 'serial', data.serial, {});
    html += ticketScanRow('Task (Task Description)', 'task', data.task, { required: true, list: 'ticket-scan-task-list' });
    html += ticketScanRow('Problem (Reported Problem)', 'problem', data.problem, { multiline: true });
    html += ticketScanRow('Customer / Contact Person', 'customer', data.customer, {});
    html += ticketScanRow('Address', 'address', data.address, { multiline: true });
    html += ticketScanRow('Note (optional)', 'note', data.note, {});
    html += ticketScanDatalist('ticket-scan-company-list', ticketScanOptionValues('tt-company'));
    html += ticketScanDatalist('ticket-scan-task-list', ticketScanOptionValues('tt-task'));
    html += ticketScanDatalist('ticket-scan-model-list', ticketScanModelNames());
    wrap.innerHTML = html;
    Array.prototype.forEach.call(wrap.querySelectorAll('[data-scan-use]'), function(btn) {
        btn.addEventListener('click', function() {
            var input = wrap.querySelector('[data-scan-field="ticket_no"]');
            if (!input) return;
            input.value = btn.getAttribute('data-scan-use') || '';
            try { input.dispatchEvent(new Event('input', { bubbles: true })); } catch (e) {}
            ticketScanRenderMissing();
        });
    });
    Array.prototype.forEach.call(wrap.querySelectorAll('[data-scan-field]'), function(el) {
        el.addEventListener('input', function() { ticketScanRenderMissing(); });
        el.addEventListener('change', function() { ticketScanRenderMissing(); });
    });
    ticketScanRenderMissing();
}

function ticketScanCollect() {
    var out = {};
    Array.prototype.forEach.call(document.querySelectorAll('#ticket-scan-results [data-scan-field]'), function(el) {
        out[el.getAttribute('data-scan-field')] = String(el.value || '').trim();
    });
    return out;
}

function ticketScanRenderMissing() {
    var values = ticketScanCollect();
    var missing = [];
    if (!values.ticket_no) missing.push('Ticket No.');
    if (!values.company) missing.push('Company');
    if (!values.task) missing.push('Task');
    if (!values.device_type) missing.push('Device');
    var box = ticketScanEl('ticket-scan-missing');
    if (!box) return;
    if (!missing.length) { box.style.display = 'none'; box.innerHTML = ''; return; }
    box.innerHTML = '<b>Not printed on the work order:</b> ' + escHtml(missing.join(', '))
        + ' — type or pick these on the ticket form as usual.';
    box.style.display = '';
}

// Copy the raw OCR text so a misread can be reported without retyping it.
function ticketScanCopyRaw() {
    var text = ticketScanRawText || '(nothing was read)';
    function report(ok) {
        showToast(ok
            ? 'Scanned text copied — paste it into your message to support.'
            : 'Copying is blocked here. Tap "Show / hide raw scanned text" and copy it by hand.',
            ok ? 'success' : 'warning');
    }
    try {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function() {
                report(true);
            }, function() {
                report(false);
            });
            return;
        }
    } catch (e) {}
    report(false);
}

function ticketScanToggleRaw() {
    var pre = ticketScanEl('ticket-scan-raw');
    if (!pre) return;
    var show = pre.style.display === 'none';
    pre.style.display = show ? '' : 'none';
    if (show) pre.textContent = ticketScanRawText || '(nothing was read)';
}

// ==================== Apply the scan to the New Ticket form ====================
function ticketScanNormalize(value) {
    if (typeof ttNormalizeName === 'function') return ttNormalizeName(value);
    return String(value || '').toLowerCase().replace(/\s+/g, ' ').trim();
}

function ticketScanMatchSelectOption(selectId, value) {
    var sel = ticketScanEl(selectId);
    var match = '';
    if (!sel || !value) return '';
    var wanted = ticketScanNormalize(value);
    Array.prototype.forEach.call(sel.options, function(opt) {
        if (match) return;
        var optionValue = String(opt.value || '');
        if (!optionValue || optionValue === '__OTHER__') return;
        if (ticketScanNormalize(optionValue) === wanted) match = optionValue;
    });
    return match;
}

// Dropdown entries that already exist are reused; anything new is typed into the
// "Other…" box so the approval flow stays exactly as it was.
function ticketScanApplySuggestion(selectId, otherId, hintId, value, hintText) {
    var sel = ticketScanEl(selectId);
    var other = ticketScanEl(otherId);
    var hint = ticketScanEl(hintId);
    if (!sel || !value) return;
    var match = ticketScanMatchSelectOption(selectId, value);
    if (match) {
        sel.value = match;
        if (other) { other.value = ''; other.style.display = 'none'; }
        if (hint) { hint.textContent = ''; hint.style.display = 'none'; }
        return;
    }
    sel.value = '__OTHER__';
    if (other) { other.value = value; other.style.display = ''; }
    if (hint) {
        hint.textContent = hintText || 'This will be pending approval after ticket creation.';
        hint.style.display = '';
    }
}

function ticketScanApplyDeviceType(type) {
    if (!type) return;
    ttSelectedDeviceType = type;
    var hidden = ticketScanEl('tt-device-type');
    if (hidden) hidden.value = type;
    if (typeof ttPopulateDeviceDropdown === 'function') {
        ttSelectedDeviceType = type;
        ttPopulateDeviceDropdown();
    }
    var sel = ticketScanEl('tt-device');
    var other = ticketScanEl('tt-device-other');
    if (!sel) return;
    var found = false;
    Array.prototype.forEach.call(sel.options, function(opt) {
        if (opt.value === type) { sel.value = type; found = true; }
    });
    if (!found) {
        sel.value = '__OTHER__';
        if (other) { other.value = type; other.style.display = ''; }
    } else if (other) {
        other.value = '';
        other.style.display = 'none';
    }
}

// What is still blank on the ticket form itself after the scan was applied.
function ticketScanFormMissing() {
    var missing = [];
    var ticketNo = ticketScanEl('tt-ticket-no');
    if (!ticketNo || !/\d/.test(ticketNo.value || '')) missing.push('Ticket No.');
    var company = (typeof ttCompanyValue === 'function') ? ttCompanyValue() : '';
    if (!company) missing.push('Company Name');
    var task = (typeof ttTaskValue === 'function') ? ttTaskValue() : '';
    if (!task) missing.push('Task');
    if (!String(ttSelectedDeviceType || '').trim()) missing.push('Device');
    var address = ticketScanEl('tt-address');
    if (!address || !String(address.value || '').trim()) missing.push('Address');
    return missing;
}

function ticketScanApply() {
    var values = ticketScanCollect();
    var equipment = ticketScanMatchEquipment(values.serial, values.model || '');
    var ticketDigits = String(values.ticket_no || '').replace(/\D/g, '');

    if (ticketDigits) ticketScanSetValue('tt-ticket-no', ticketDigits);
    if (values.serial) ticketScanSetValue('tt-serial', values.serial);

    // Company: reuse an approved dropdown entry when it already exists.
    if (values.company) {
        ticketScanApplySuggestion('tt-company', 'tt-company-other', 'tt-company-hint', values.company);
        if (typeof ttRenderAddressDatalist === 'function') ttRenderAddressDatalist();
    }

    // Device: prefer the real equipment record (serial / model match). It also
    // restores the serial exactly as stored, which fixes O/0 and I/1 OCR slips.
    if (equipment && typeof ttSelectEquipment === 'function') {
        ttSelectEquipment(parseInt(equipment.id, 10) || 0);
    } else {
        ticketScanApplyDeviceType(values.device_type);
        if (values.model) {
            var modelInput = ticketScanEl('tt-model-search');
            if (modelInput) modelInput.value = values.model;
            if (typeof ttModelFreeText === 'function') ttModelFreeText(values.model);
        }
    }

    // The rest of the printed work order.
    if (values.task) {
        ticketScanApplySuggestion('tt-task', 'tt-task-other', 'tt-task-hint', values.task);
    }
    if (values.problem) {
        ticketScanSetValue('tt-issue-custom', values.problem);
        var issueSel = ticketScanEl('tt-issue');
        if (issueSel) issueSel.value = '';
        if (typeof ttIssueChanged === 'function') ttIssueChanged();
    }
    if (values.customer) ticketScanSetValue('tt-customer', values.customer);
    if (values.address) {
        ticketScanSetValue('tt-address', values.address);
        if (typeof ttResolveDestinationAddress === 'function') {
            try { ttResolveDestinationAddress(values.address); } catch (e) {}
        }
    }
    if (values.note) ticketScanSetValue('tt-note', values.note);

    ticketScanClose();
    var missing = ticketScanFormMissing();
    if (missing.length) {
        showToast('Work order scanned. Still to fill in: ' + missing.join(', ') + '.', 'warning');
    } else {
        // Deliberately NOT a green "success": the scan only fills the form —
        // the ticket is not saved until Create Ticket is pressed on step 2.
        showToast('Work order scanned — the form is filled in, but nothing is saved yet. Check the values, then tap Next and Create Ticket.', 'info');
    }
}

// The global Escape shortcut hides every modal, so release the camera too.
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') ticketScanStopCamera();
});
