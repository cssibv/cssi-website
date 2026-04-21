/**
 * ═══════════════════════════════════════════════════════════════
 * CSSI MARKETING MODULE v1.0 — Extensie pentru Portal CSSI v3.0
 * ═══════════════════════════════════════════════════════════════
 *
 * Se lipeste la finalul Cod.gs din Portal CSSI v3.0.
 * Nu modifica nimic din codul existent — doar adauga functii noi.
 *
 * SETUP:
 * 1. Adauga in Sheet-ul central 2 tab-uri noi:
 *    - MARKETING: Data | Canal | Campanie | Impresii | Clicuri | CTR | Cost | Conversii | CPL | Note
 *    - KPI_DASHBOARD: (auto-generate — nu seta manual headere)
 *
 * 2. Adauga in sheet-ul CONFIG urmatoarele chei:
 *    - GOOGLE_ADS_CUSTOMER_ID
 *    - GA4_PROPERTY_ID
 *    - GSC_SITE_URL
 *    - META_AD_ACCOUNT_ID (optional, doar cand pornim Meta Ads)
 *    - TARGET_CPL_RON (ex: 25)
 *    - ALERT_EMAIL (email-ul tau)
 *    - TARGET_KEYWORDS (comma-separated: "pontaj electronic,camere supraveghere brasov,sistem alarma firma")
 *
 * 3. Activeaza in Apps Script:
 *    Services (+) → Google Ads API, Analytics Data API, Search Console API
 *
 * 4. Seteaza triggere (Edit → Current project triggers):
 *    - syncMarketingData — daily 07:30
 *    - sapRaport — weekly Monday 07:30
 *    - lunarRaport — monthly day 1 at 07:00
 *    - checkCPL — daily 11:00
 *    - checkRankDrop — daily 11:00
 *    - keywordIdeas — weekly Friday 10:00
 *
 * 5. Prima rulare: click Run pe syncMarketingData (autorizeaza toate permisiunile)
 * ═══════════════════════════════════════════════════════════════
 */

// ──────────────────────────────────────────────────────────────
// 1. SYNC MARKETING DATA (zilnic 07:30)
// ──────────────────────────────────────────────────────────────
function syncMarketingData() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const mkt = ss.getSheetByName('MARKETING') || ss.insertSheet('MARKETING');
  if (mkt.getLastRow() === 0) {
    mkt.appendRow(['Data','Canal','Campanie','Impresii','Clicuri','CTR','Cost','Conversii','CPL','Note']);
  }

  const today = Utilities.formatDate(new Date(), 'Europe/Bucharest', 'yyyy-MM-dd');

  // ── Google Ads ──
  try {
    const adsData = fetchGoogleAdsYesterday_();
    adsData.forEach(row => {
      const ctr = row.impressions > 0 ? (row.clicks / row.impressions * 100).toFixed(2) + '%' : '0%';
      const cpl = row.conversions > 0 ? (row.cost / row.conversions).toFixed(2) : '-';
      mkt.appendRow([today, 'Google Ads', row.campaign, row.impressions, row.clicks, ctr, row.cost, row.conversions, cpl, '']);
    });
  } catch(e) { Logger.log('Google Ads sync error: ' + e.message); }

  // ── GA4 ──
  try {
    const ga4 = fetchGA4Yesterday_();
    mkt.appendRow([today, 'GA4 Total', 'organic+paid', ga4.impressions || 0, ga4.users, '-', 0, ga4.conversions, '-', 'engagement ' + ga4.avgEngagement + 's']);
  } catch(e) { Logger.log('GA4 sync error: ' + e.message); }

  // ── Search Console ──
  try {
    const gsc = fetchGSCYesterday_();
    mkt.appendRow([today, 'GSC (organic)', 'all queries', gsc.impressions, gsc.clicks, (gsc.ctr*100).toFixed(2)+'%', 0, 0, '-', 'pos ' + gsc.position.toFixed(1)]);
  } catch(e) { Logger.log('GSC sync error: ' + e.message); }

  // ── Regenerare dashboard ──
  generateKpiDashboard();
}

// ──────────────────────────────────────────────────────────────
// 2. GOOGLE ADS API — fetch yesterday data per campaign
// ──────────────────────────────────────────────────────────────
function fetchGoogleAdsYesterday_() {
  const cfg = getConfig_();
  if (!cfg.GOOGLE_ADS_CUSTOMER_ID) return [];

  // Foloseste GoogleAds API prin UrlFetchApp (simplificat — foloseste token de dezvoltator)
  // NOTA: Pentru implementare completa, foloseste librăria GoogleAdsApp din Scripts
  // Aceasta este versiunea simplificata pentru demo
  const yesterday = new Date(); yesterday.setDate(yesterday.getDate() - 1);
  const dateStr = Utilities.formatDate(yesterday, 'Europe/Bucharest', 'yyyy-MM-dd');

  // Placeholder — va fi completat cu call real la Google Ads API GAQL
  // Exemplu query:
  // SELECT campaign.name, metrics.impressions, metrics.clicks, metrics.cost_micros, metrics.conversions
  // FROM campaign WHERE segments.date = '{dateStr}'

  return []; // Intoarce array gol in MVP; se completeaza dupa ce obtinem dev_token
}

// ──────────────────────────────────────────────────────────────
// 3. GA4 DATA API — fetch yesterday
// ──────────────────────────────────────────────────────────────
function fetchGA4Yesterday_() {
  const cfg = getConfig_();
  if (!cfg.GA4_PROPERTY_ID) return { users:0, conversions:0, avgEngagement:0 };

  const yesterday = new Date(); yesterday.setDate(yesterday.getDate() - 1);
  const dateStr = Utilities.formatDate(yesterday, 'Europe/Bucharest', 'yyyy-MM-dd');

  const request = {
    dateRanges: [{ startDate: dateStr, endDate: dateStr }],
    metrics: [
      { name: 'activeUsers' },
      { name: 'conversions' },
      { name: 'averageSessionDuration' },
    ],
  };

  try {
    const response = AnalyticsData.Properties.runReport(request, 'properties/' + cfg.GA4_PROPERTY_ID);
    const row = response.rows && response.rows[0] ? response.rows[0].metricValues : null;
    return {
      users: row ? parseInt(row[0].value) : 0,
      conversions: row ? parseFloat(row[1].value) : 0,
      avgEngagement: row ? parseFloat(row[2].value).toFixed(0) : 0,
    };
  } catch(e) {
    Logger.log('GA4 API error: ' + e.message);
    return { users:0, conversions:0, avgEngagement:0 };
  }
}

// ──────────────────────────────────────────────────────────────
// 4. SEARCH CONSOLE API — fetch yesterday aggregate
// ──────────────────────────────────────────────────────────────
function fetchGSCYesterday_() {
  const cfg = getConfig_();
  if (!cfg.GSC_SITE_URL) return { impressions:0, clicks:0, ctr:0, position:0 };

  const yesterday = new Date(); yesterday.setDate(yesterday.getDate() - 1);
  const dateStr = Utilities.formatDate(yesterday, 'Europe/Bucharest', 'yyyy-MM-dd');

  const request = {
    startDate: dateStr,
    endDate: dateStr,
    dimensions: [],
    rowLimit: 1,
  };

  try {
    const response = SearchConsole.Searchanalytics.query(request, cfg.GSC_SITE_URL);
    const row = response.rows && response.rows[0];
    return row ? {
      impressions: row.impressions,
      clicks: row.clicks,
      ctr: row.ctr,
      position: row.position,
    } : { impressions:0, clicks:0, ctr:0, position:0 };
  } catch(e) {
    Logger.log('GSC API error: ' + e.message);
    return { impressions:0, clicks:0, ctr:0, position:0 };
  }
}

// ──────────────────────────────────────────────────────────────
// 5. KPI DASHBOARD GENERATOR
// ──────────────────────────────────────────────────────────────
function generateKpiDashboard() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const dash = ss.getSheetByName('KPI_DASHBOARD') || ss.insertSheet('KPI_DASHBOARD');
  const mkt = ss.getSheetByName('MARKETING');
  if (!mkt || mkt.getLastRow() < 2) return;

  dash.clear();

  // Header
  dash.getRange('A1').setValue('CSSI Marketing Dashboard').setFontSize(18).setFontWeight('bold');
  dash.getRange('A2').setValue('Ultima actualizare: ' + new Date().toLocaleString('ro-RO'));

  // Last 30 days aggregation
  const data = mkt.getRange(2, 1, mkt.getLastRow()-1, 10).getValues();
  const thirtyDaysAgo = new Date(); thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);

  let totalCost=0, totalConv=0, totalUsers=0, totalClicks=0;
  data.forEach(r => {
    const rowDate = new Date(r[0]);
    if (rowDate < thirtyDaysAgo) return;
    totalCost += parseFloat(r[6]) || 0;
    totalConv += parseFloat(r[7]) || 0;
    totalClicks += parseInt(r[4]) || 0;
    if (r[1] === 'GA4 Total') totalUsers += parseInt(r[4]) || 0;
  });

  const cpl = totalConv > 0 ? (totalCost/totalConv) : 0;
  const cfg = getConfig_();
  const targetCPL = parseFloat(cfg.TARGET_CPL_RON) || 25;
  const cplStatus = cpl === 0 ? '⚪' : (cpl <= targetCPL ? '🟢' : (cpl <= targetCPL*1.6 ? '🟡' : '🔴'));

  dash.getRange('A4').setValue('📊 KPI ULTIMELE 30 ZILE').setFontWeight('bold').setFontSize(14);

  const rows = [
    ['', ''],
    ['Utilizatori GA4', totalUsers],
    ['Clicuri Google Ads', totalClicks],
    ['Conversii', totalConv],
    ['Cost total (RON)', totalCost.toFixed(2)],
    ['CPL (RON)', cpl > 0 ? cpl.toFixed(2) : '-'],
    ['CPL Status', cplStatus + ' (țintă ' + targetCPL + ' RON)'],
  ];
  dash.getRange(5, 1, rows.length, 2).setValues(rows);

  dash.getRange('A14').setValue('🎯 Urmează să faci:').setFontWeight('bold').setFontSize(14);
  const actions = [];
  if (cpl > targetCPL * 1.5) actions.push(['⚠️ CPL prea mare — pauză campanii slabe și focus pe top 3 performere.']);
  if (totalConv < 10 && data.length > 20) actions.push(['⚠️ Puține conversii — verifică dacă formularele tracking sunt active.']);
  if (totalUsers < 500) actions.push(['📈 Crește trafic organic: publică 1 blog post săptămâna asta.']);
  actions.push(['✅ Rulează manual keywordIdeas() vinerea pentru sugestii noi.']);
  dash.getRange(15, 1, actions.length, 1).setValues(actions);

  dash.autoResizeColumns(1, 2);
}

// ──────────────────────────────────────────────────────────────
// 6. RAPORT SĂPTĂMÂNAL (Luni 07:30)
// ──────────────────────────────────────────────────────────────
function sapRaport() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const mkt = ss.getSheetByName('MARKETING');
  const cfg = getConfig_();
  if (!mkt || !cfg.ALERT_EMAIL) return;

  const data = mkt.getRange(2, 1, mkt.getLastRow()-1, 10).getValues();
  const week = new Date(); week.setDate(week.getDate() - 7);
  const twoWeek = new Date(); twoWeek.setDate(twoWeek.getDate() - 14);

  let w1 = {cost:0, conv:0, users:0};
  let w2 = {cost:0, conv:0, users:0};
  data.forEach(r => {
    const d = new Date(r[0]);
    const bucket = d >= week ? w1 : (d >= twoWeek ? w2 : null);
    if (!bucket) return;
    bucket.cost += parseFloat(r[6]) || 0;
    bucket.conv += parseFloat(r[7]) || 0;
    if (r[1] === 'GA4 Total') bucket.users += parseInt(r[4]) || 0;
  });

  const pct = (a, b) => b === 0 ? 'N/A' : ((a-b)/b*100).toFixed(1) + '%';

  const body = `
<h2>📊 Raport Săptămânal CSSI — Marketing</h2>
<p><b>Perioada:</b> ${Utilities.formatDate(week,'Europe/Bucharest','dd MMM')} – azi</p>

<table border="1" cellpadding="8" style="border-collapse:collapse">
<tr style="background:#0D3C61;color:#fff"><th>Metric</th><th>Săptămâna asta</th><th>Săptămâna trecută</th><th>Variație</th></tr>
<tr><td>Utilizatori GA4</td><td>${w1.users}</td><td>${w2.users}</td><td>${pct(w1.users,w2.users)}</td></tr>
<tr><td>Conversii</td><td>${w1.conv}</td><td>${w2.conv}</td><td>${pct(w1.conv,w2.conv)}</td></tr>
<tr><td>Cost (RON)</td><td>${w1.cost.toFixed(2)}</td><td>${w2.cost.toFixed(2)}</td><td>${pct(w1.cost,w2.cost)}</td></tr>
</table>

<p><a href="${ss.getUrl()}">Deschide dashboard-ul complet</a></p>
`;

  MailApp.sendEmail({
    to: cfg.ALERT_EMAIL,
    subject: '📊 Raport Săptămânal CSSI — ' + Utilizari_(w1),
    htmlBody: body,
  });
}
function Utilizari_(w) { return w.conv + ' conversii, ' + w.users + ' utilizatori'; }

// ──────────────────────────────────────────────────────────────
// 7. RAPORT LUNAR (ziua 1 la 07:00)
// ──────────────────────────────────────────────────────────────
function lunarRaport() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const mkt = ss.getSheetByName('MARKETING');
  const cfg = getConfig_();
  if (!mkt || !cfg.ALERT_EMAIL) return;

  const data = mkt.getRange(2, 1, mkt.getLastRow()-1, 10).getValues();
  const monthStart = new Date(); monthStart.setDate(1); monthStart.setMonth(monthStart.getMonth()-1);
  const monthEnd = new Date(); monthEnd.setDate(0);

  let totals = {cost:0, conv:0, users:0, clicks:0, impressions:0};
  data.forEach(r => {
    const d = new Date(r[0]);
    if (d < monthStart || d > monthEnd) return;
    totals.cost += parseFloat(r[6]) || 0;
    totals.conv += parseFloat(r[7]) || 0;
    totals.clicks += parseInt(r[4]) || 0;
    totals.impressions += parseInt(r[3]) || 0;
    if (r[1] === 'GA4 Total') totals.users += parseInt(r[4]) || 0;
  });

  const cpl = totals.conv > 0 ? (totals.cost/totals.conv).toFixed(2) : 'N/A';
  const ctr = totals.impressions > 0 ? (totals.clicks/totals.impressions*100).toFixed(2) : 0;

  const body = `
<h1>📅 Raport Lunar CSSI — Marketing</h1>
<p>Luna: <b>${Utilities.formatDate(monthStart,'Europe/Bucharest','MMMM yyyy')}</b></p>

<h2>Rezumat executiv</h2>
<ul>
  <li>👥 Utilizatori: <b>${totals.users}</b></li>
  <li>🖱️ Clicuri plătite: <b>${totals.clicks}</b></li>
  <li>💰 Cost total: <b>${totals.cost.toFixed(2)} RON</b></li>
  <li>🎯 Conversii: <b>${totals.conv}</b></li>
  <li>💸 CPL: <b>${cpl} RON</b> (țintă: ${cfg.TARGET_CPL_RON || 25})</li>
  <li>📈 CTR: <b>${ctr}%</b></li>
</ul>

<h2>Recomandări luna viitoare</h2>
<ul>
  <li>Revizuiește campaniile cu CPL &gt; țintă și pune-le pe pauză sau ajustează bidding.</li>
  <li>Verifică top 5 queries GSC și optimizează meta descriptions pentru cele cu CTR &lt; 2%.</li>
  <li>Publică 2 blog posts noi pe tematici din keywordIdeas.</li>
  <li>Dacă total conversii &lt; țintă lunară, crește bugetul pe top campanie cu 30%.</li>
</ul>

<p><a href="${ss.getUrl()}">Deschide dashboard-ul complet</a></p>
`;

  MailApp.sendEmail({
    to: cfg.ALERT_EMAIL,
    subject: '📅 Raport Lunar CSSI — ' + Utilities.formatDate(monthStart,'Europe/Bucharest','MMMM yyyy'),
    htmlBody: body,
  });
}

// ──────────────────────────────────────────────────────────────
// 8. ALERTĂ CPL RIDICAT (zilnic 11:00)
// ──────────────────────────────────────────────────────────────
function checkCPL() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const mkt = ss.getSheetByName('MARKETING');
  const cfg = getConfig_();
  const threshold = parseFloat(cfg.TARGET_CPL_RON || 25) * 2; // alert daca > 2x ținta
  if (!mkt || !cfg.ALERT_EMAIL) return;

  // Verifică ultimele 3 zile Google Ads
  const data = mkt.getRange(2, 1, mkt.getLastRow()-1, 10).getValues();
  const threeDays = new Date(); threeDays.setDate(threeDays.getDate() - 3);
  let badDays = 0;
  data.forEach(r => {
    const d = new Date(r[0]);
    if (d < threeDays) return;
    if (r[1] !== 'Google Ads') return;
    const cpl = parseFloat(r[8]);
    if (cpl && cpl > threshold) badDays++;
  });

  if (badDays >= 3) {
    MailApp.sendEmail({
      to: cfg.ALERT_EMAIL,
      subject: '🚨 ALERT: CPL peste prag 3 zile consecutiv',
      body: 'CPL-ul este peste ' + threshold + ' RON de 3+ zile. Intră în Google Ads și pune pe pauză campaniile slabe.',
    });
  }
}

// ──────────────────────────────────────────────────────────────
// 9. VERIFICĂ SCĂDERI DE POZIȚII SEO (zilnic 11:00)
// ──────────────────────────────────────────────────────────────
function checkRankDrop() {
  const cfg = getConfig_();
  if (!cfg.TARGET_KEYWORDS || !cfg.GSC_SITE_URL) return;
  const keywords = cfg.TARGET_KEYWORDS.split(',').map(k => k.trim());

  const ss = SpreadsheetApp.getActiveSpreadsheet();
  let rankSheet = ss.getSheetByName('RANK_HISTORY');
  if (!rankSheet) {
    rankSheet = ss.insertSheet('RANK_HISTORY');
    rankSheet.appendRow(['Data','Keyword','Poziție','Afișări','Clicuri']);
  }

  const today = Utilities.formatDate(new Date(), 'Europe/Bucharest', 'yyyy-MM-dd');
  const sevenDaysAgo = new Date(); sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);

  keywords.forEach(kw => {
    try {
      const request = {
        startDate: Utilities.formatDate(sevenDaysAgo, 'Europe/Bucharest', 'yyyy-MM-dd'),
        endDate: today,
        dimensions: ['query'],
        dimensionFilterGroups: [{ filters: [{ dimension:'query', operator:'equals', expression: kw }] }],
        rowLimit: 1,
      };
      const r = SearchConsole.Searchanalytics.query(request, cfg.GSC_SITE_URL);
      const row = r.rows && r.rows[0];
      if (row) {
        rankSheet.appendRow([today, kw, row.position.toFixed(1), row.impressions, row.clicks]);

        // Alert daca pozitia a scazut cu peste 5
        const prev = rankSheet.getDataRange().getValues().filter(x => x[1] === kw);
        if (prev.length >= 2) {
          const curr = parseFloat(prev[prev.length-1][2]);
          const before = parseFloat(prev[prev.length-2][2]);
          if (curr - before > 5) {
            MailApp.sendEmail({
              to: cfg.ALERT_EMAIL,
              subject: '📉 ALERT SEO: "' + kw + '" a scăzut ' + (curr - before).toFixed(0) + ' poziții',
              body: kw + ' a trecut de la poziția ' + before.toFixed(1) + ' la ' + curr.toFixed(1) + '. Verifică in GSC.',
            });
          }
        }
      }
    } catch(e) { Logger.log('Rank check error for ' + kw + ': ' + e.message); }
  });
}

// ──────────────────────────────────────────────────────────────
// 10. SUGESTII KEYWORDS NOI (vineri 10:00)
// ──────────────────────────────────────────────────────────────
function keywordIdeas() {
  const cfg = getConfig_();
  if (!cfg.GSC_SITE_URL || !cfg.ALERT_EMAIL) return;

  const today = new Date();
  const thirtyAgo = new Date(); thirtyAgo.setDate(thirtyAgo.getDate() - 30);

  const request = {
    startDate: Utilities.formatDate(thirtyAgo, 'Europe/Bucharest', 'yyyy-MM-dd'),
    endDate: Utilities.formatDate(today, 'Europe/Bucharest', 'yyyy-MM-dd'),
    dimensions: ['query'],
    rowLimit: 50,
  };

  try {
    const r = SearchConsole.Searchanalytics.query(request, cfg.GSC_SITE_URL);
    // Filtrare: queries cu > 10 afișări dar < 3 clicuri (opportunity)
    const opps = (r.rows || [])
      .filter(row => row.impressions >= 10 && row.clicks < 3)
      .sort((a,b) => b.impressions - a.impressions)
      .slice(0, 10);

    if (opps.length === 0) return;

    let html = '<h2>🎯 Top 10 oportunități de keywords</h2><table border="1" cellpadding="6"><tr><th>Query</th><th>Afișări</th><th>Clicuri</th><th>Poziție</th></tr>';
    opps.forEach(o => {
      html += `<tr><td>${o.keys[0]}</td><td>${o.impressions}</td><td>${o.clicks}</td><td>${o.position.toFixed(1)}</td></tr>`;
    });
    html += '</table><p>Sugestie: scrie un blog post sau optimizează landing page pentru top 3 queries.</p>';

    MailApp.sendEmail({
      to: cfg.ALERT_EMAIL,
      subject: '💡 Keywords de urmărit săptămâna viitoare',
      htmlBody: html,
    });
  } catch(e) { Logger.log('Keyword ideas error: ' + e.message); }
}

// ──────────────────────────────────────────────────────────────
// HELPER — Read CONFIG sheet into object
// ──────────────────────────────────────────────────────────────
function getConfig_() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const cfgSheet = ss.getSheetByName('CONFIG');
  if (!cfgSheet) return {};
  const data = cfgSheet.getDataRange().getValues();
  const out = {};
  data.forEach(r => { if (r[0]) out[r[0]] = r[1]; });
  return out;
}
