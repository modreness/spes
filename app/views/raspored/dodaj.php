<div class="naslov-dugme">
    <h2>Dodaj novi raspored</h2>
    <a href="/raspored" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Povratak</a>
</div>

<div class="main-content">
    <form action="/raspored/dodaj" method="post" class="rasporedi-table">
        <table class="table-standard">
            <tr>
                <td><strong>Terapeut</strong></td>
                <td>
                    <select name="terapeut_id" class="select2" required>
                        <option value="">Odaberite terapeuta</option>
                        <?php foreach ($terapeuti as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['ime'] . ' ' . $t['prezime']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><strong>Datum početka sedmice (ponedjeljak)</strong></td>
                <td><input type="date" name="datum_od" required value="<?= date('Y-m-d', strtotime('monday this week')) ?>"></td>
            </tr>
        </table>

        <h3>Raspored po danima</h3>
        <table class="table-standard">
            <thead>
                <tr>
                    <th>Dan</th>
                    <th>Smjena</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (dani() as $key => $label): ?>
                <tr>
                    <td><strong><?= $label ?></strong></td>
                    <td>
                        <select name="raspored[<?= $key ?>][smjena]">
                            <option value="">Ne radi</option>
                            <?php foreach (smjene() as $smjenaKey => $smjenaLabel): ?>
                                <option value="<?= $smjenaKey ?>"><?= $smjenaLabel ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="form-actions">
            <button type="submit" name="snimi" class="btn btn-add">
                <i class="fa-solid fa-save"></i> Snimi raspored
            </button>
            <a href="/raspored" class="btn btn-secondary">Otkaži</a>
        </div>
    </form>
</div>