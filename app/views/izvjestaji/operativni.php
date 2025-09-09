<div class="naslov-dugme">
    <h2>Operativni izvještaj</h2>
    <a href="/izvjestaji" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Povratak</a>
</div>

<div class="main-content-fw">
    <!-- Filteri -->
    <div style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 25px;">
        <form method="get">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end;">
                
                <div class="form-group">
                    <label for="period">Period</label>
                    <select id="period" name="period" onchange="toggleCustomDates()">
                        <option value="ova_sedmica" <?= $period === 'ova_sedmica' ? 'selected' : '' ?>>Ova sedmica</option>
                        <option value="ovaj_mesec" <?= $period === 'ovaj_mesec' ? 'selected' : '' ?>>Ovaj mesec</option>
                        <option value="prosli_mesec" <?= $period === 'prosli_mesec' ? 'selected' : '' ?>>Prošli mesec</option>
                        <option value="custom" <?= $period === 'custom' ? 'selected' : '' ?>>Prilagođeno</option>
                    </select>
                </div>
                
                <div class="form-group" id="custom-dates" style="display: <?= $period === 'custom' ? 'block' : 'none' ?>;">
                    <label for="datum_od">Od - Do</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="date" id="datum_od" name="datum_od" value="<?= htmlspecialchars($datum_od) ?>">
                        <input type="date" id="datum_do" name="datum_do" value="<?= htmlspecialchars($datum_do) ?>">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Generiši izvještaj</button>
            </div>
        </form>
    </div>

    <!-- Ostatak koda ostaje isti... -->