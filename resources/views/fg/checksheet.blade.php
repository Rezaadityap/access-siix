<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Checksheet UI - Upload Preview</title>
    <style>
        :root {
            --border: #222;
            --muted: #666;
            --bg: #f7f7f8;
            --card: #fff;
        }

        body {
            font-family: Inter, Roboto, Arial, sans-serif;
            background: var(--bg);
            margin: 0;
            padding: 28px;
            color: #111;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
        }

        h1 {
            text-align: center;
            margin: 0 0 18px;
            font-size: 28px;
            letter-spacing: 1px;
        }

        /* Description card */
        .desc-card {
            background: var(--card);
            border: 2px solid var(--border);
            border-radius: 10px;
            padding: 16px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
            margin-bottom: 18px;
        }

        .desc-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            align-items: center;
        }

        .desc-field label {
            display: block;
            font-size: 12px;
            color: var(--muted);
            margin-bottom: 6px;
        }

        .desc-field input,
        .desc-field select {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            background: #fff;
        }

        /* sheet area */
        .sheet {
            background: #fff;
            border: 3px solid var(--border);
            border-radius: 6px;
            padding: 14px;
            box-sizing: border-box;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
        }

        .cell {
            min-height: 140px;
            border: 2px solid #bbb;
            border-radius: 8px;
            background: linear-gradient(180deg, #fff, #fafafa);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .cell .placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            color: var(--muted);
        }

        .add-btn {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            border: 2px dashed #999;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            cursor: pointer;
            background: #fff;
        }

        .caption {
            font-weight: 600;
            font-size: 13px;
            margin-top: 6px;
            text-align: center;
        }

        .cell img.preview {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .actions {
            position: absolute;
            top: 8px;
            right: 8px;
            display: flex;
            gap: 6px;
            z-index: 5;
        }

        .btn-icon {
            background: rgba(0, 0, 0, 0.5);
            color: #fff;
            border: none;
            width: 34px;
            height: 34px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        /* small label under the sheet */
        .legend {
            margin-top: 10px;
            color: var(--muted);
            font-size: 13px;
            text-align: center;
        }

        /* responsive */
        @media (max-width:900px) {
            .grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width:460px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>CHECK SHEET DOKUMENTASI EXPORT</h1>

        <!-- Description card (atas) -->
        <div class="desc-card" id="descCard">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                <strong>Deskripsi</strong>
                <small style="color:var(--muted)">Isi metadata (opsional)</small>
            </div>

            <div class="desc-grid">
                <div class="desc-field">
                    <label>Tanggal</label>
                    <input id="in_date" type="date">
                </div>
                <div class="desc-field">
                    <label>ETD Time</label>
                    <input id="in_time" type="time">
                </div>
                <div class="desc-field">
                    <label>Export To</label>
                    <input id="in_exportto" type="text" placeholder="Contoh: ICHIKOH">
                </div>
                <div class="desc-field">
                    <label>Country</label>
                    <input id="in_country" type="text" placeholder="Contoh: MALAYSIA">
                </div>
                <div class="desc-field">
                    <label>FWD</label>
                    <input id="in_fwd" type="text" placeholder="Contoh: CHEVA">
                </div>
                <div class="desc-field">
                    <label>Prepared by</label>
                    <input id="in_prepared" type="text">
                </div>
                <div class="desc-field">
                    <label>Checked by</label>
                    <input id="in_checked" type="text">
                </div>
                <div class="desc-field">
                    <label>Approved by</label>
                    <input id="in_approved" type="text">
                </div>
            </div>
        </div>

        <!-- sheet area with 8 empty boxes -->
        <div class="sheet" style="margin-top:16px;">
            <div style="margin-bottom:10px; font-weight:600; text-align:center;">Dokumentasi (Klik + untuk upload foto)
            </div>

            <div class="grid" id="cellsGrid">
                <!-- We'll inject 8 cells via JS for easier labeling -->
            </div>

            <div class="legend">Semua foto hanya preview di sisi client (tidak tersimpan ke server dalam contoh ini).
            </div>
        </div>
    </div>

    <!-- single hidden input yang dipakai ulang -->
    <input type="file" accept="image/*" id="filePicker" style="display:none" />

    <script>
        // definisi 8 cell (id -> label)
        const cellDefs = [{
                id: 'nomor_segel',
                label: 'Nomor Segel'
            },
            {
                id: 'surat_jalan',
                label: 'Surat Jalan'
            },
            {
                id: 'nomor_kontainer',
                label: 'Nomor Kontainer / No pol'
            },
            {
                id: 'segel_terpasang',
                label: 'Segel Terpasang'
            },
            {
                id: 'kontainer_kosong',
                label: 'Kontainer Kosong'
            },
            {
                id: 'kontainer_1_4',
                label: 'Kontainer Terisi 1/4'
            },
            {
                id: 'kontainer_1_2',
                label: 'Kontainer Terisi 1/2'
            },
            {
                id: 'kontainer_penuh',
                label: 'Kontainer Terisi Penuh'
            },
        ];

        const grid = document.getElementById('cellsGrid');
        const picker = document.getElementById('filePicker');
        let activeCellId = null;

        // buat cell DOM
        cellDefs.forEach(def => {
            const cell = document.createElement('div');
            cell.className = 'cell';
            cell.id = 'cell_' + def.id;

            // actions (replace / remove) hidden initially
            const actions = document.createElement('div');
            actions.className = 'actions';
            actions.style.display = 'none';

            const btnReplace = document.createElement('button');
            btnReplace.className = 'btn-icon';
            btnReplace.title = 'Ganti Foto';
            btnReplace.innerHTML = '✎';
            btnReplace.addEventListener('click', (e) => {
                e.stopPropagation();
                activeCellId = def.id;
                picker.click();
            });

            const btnRemove = document.createElement('button');
            btnRemove.className = 'btn-icon';
            btnRemove.title = 'Hapus Foto';
            btnRemove.innerHTML = '✕';
            btnRemove.addEventListener('click', (e) => {
                e.stopPropagation();
                clearCell(def.id);
            });

            actions.appendChild(btnReplace);
            actions.appendChild(btnRemove);
            cell.appendChild(actions);

            // placeholder with + button
            const placeholder = document.createElement('div');
            placeholder.className = 'placeholder';
            placeholder.innerHTML = `
        <div class="add-btn" data-cell="${def.id}">+</div>
        <div class="caption">${def.label}</div>
      `;

            // klik area untuk buka picker
            placeholder.querySelector('.add-btn').addEventListener('click', (ev) => {
                ev.stopPropagation();
                activeCellId = def.id;
                picker.click();
            });

            // juga klik cell membuka picker (user-friendly)
            cell.addEventListener('click', () => {
                activeCellId = def.id;
                picker.click();
            });

            cell.appendChild(placeholder);
            grid.appendChild(cell);
        });

        // handle file selection
        picker.addEventListener('change', async (evt) => {
            const file = evt.target.files && evt.target.files[0];
            if (!file || !activeCellId) {
                picker.value = '';
                return;
            }

            // basic validation
            if (!file.type.startsWith('image/')) {
                alert('Pilih file gambar saja');
                picker.value = '';
                activeCellId = null;
                return;
            }
            if (file.size > 6 * 1024 * 1024) { // 6MB limit
                alert('Ukuran maksimal 6 MB');
                picker.value = '';
                activeCellId = null;
                return;
            }

            // read file as data URL for preview
            const dataUrl = await readFileAsDataURL(file);
            showPreview(activeCellId, dataUrl);
            picker.value = '';
            activeCellId = null;
        });

        function readFileAsDataURL(file) {
            return new Promise((res, rej) => {
                const r = new FileReader();
                r.onload = () => res(r.result);
                r.onerror = () => rej(new Error('File read error'));
                r.readAsDataURL(file);
            });
        }

        function showPreview(cellId, dataUrl) {
            const cell = document.getElementById('cell_' + cellId);
            if (!cell) return;

            // hide placeholder
            const placeholder = cell.querySelector('.placeholder');
            if (placeholder) placeholder.style.display = 'none';

            // create/replace img
            let img = cell.querySelector('img.preview');
            if (!img) {
                img = document.createElement('img');
                img.className = 'preview';
                cell.appendChild(img);
            }
            img.src = dataUrl;

            // show actions
            const actions = cell.querySelector('.actions');
            if (actions) actions.style.display = 'flex';
        }

        function clearCell(cellId) {
            const cell = document.getElementById('cell_' + cellId);
            if (!cell) return;
            const img = cell.querySelector('img.preview');
            if (img) img.remove();
            const placeholder = cell.querySelector('.placeholder');
            if (placeholder) placeholder.style.display = 'flex';
            const actions = cell.querySelector('.actions');
            if (actions) actions.style.display = 'none';
        }

        // optional: keyboard shortcut delete when focus on cell (nice-to-have)
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Delete') {
                // if user recently clicked a cell, try find last active? We'll skip complex state.
            }
        });
    </script>
</body>

</html>
