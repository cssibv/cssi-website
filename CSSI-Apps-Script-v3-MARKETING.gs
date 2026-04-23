/**
 * ═══════════════════════════════════════════════════════════════
 * CSSI APPS SCRIPT v3.0 — MODUL MARKETING
 * ═══════════════════════════════════════════════════════════════
 *
 * Extindere pentru Portal CSSI v3.0.
 * Nu modifică codul existent. Se lipește DUPĂ blocul principal.
 *
 * INSTALARE:
 * 1. Deschide Apps Script-ul proiectului CSSI (Portal v3.0)
 * 2. Lipește TOT codul acesta la SFÂRȘITUL fișierului existent
 * 3. Rulează manual `setupMarketingModule()` o dată — creează sheet-urile
 * 4. Setează triggere (vezi `installMarketingTriggers()` la final)
 * 5. Opțional: completează cheile API în sheet CONFIG
 *    - GOOGLE_ADS_DEV_TOKEN
 *    - GOOGLE_ADS_CUSTOMER_ID (ex: 666-033-6562)
 *    - GA4_PROPERTY_ID (ex: 525787706)
 *    - GSC_SITE_URL (ex: sc-domain:cssi.ro)
 *
 * SHEET-URI NOI CREATE AUTOMAT:
 * - MARKETING: log zilnic metrici (Data | Canal | Impresii | Clicuri | CTR | Cost | Conversii | CPL)
 * - KPI_DASHBOARD: scorecard auto-generat cu indicatori (Verde/Galben/Roșu)
 * - ALERTS: istoric alerte (CPL ridicat, poziție pierdută)
 *
 * FUNCȚII PRINCIPALE:
 * - setupMarketingModule()      - rulat o singură dată la instalare
 * - syncMarketingData()         - zilnic 07:30 (populează MARKETING)
 * - generateKpiDashboard()      - zilnic 07:45 (regenerează dashboard)
 * - sapRaport()                 - luni 07:30 (email săptămânal)
 * - lunarRaport()               - ziua 1 a lunii 07:00 (email lunar)
 * - checkCPL()                  - zilnic 11:00 (alert CPL > 50 RON 3 zile)
 * - checkRankDrop()             - zilnic 11:00 (alert keyword scade >5 poziții)
 * - keywordIdeas()              - vineri 10:00 (sugestii keyword)
 *
 * ═══════════════════════════════════════════════════════════════
 */

// ═══════ CONFIGURARE MARKETING ═══════

// Extinde obiectul SH (definit în codul principal) cu sheet-urile noi
var SH_MK = {
  MARKETING: 'MARKETING',
  KPI_DASHBOARD: 'KPI_DASHBOARD',
  ALERTS: 'MK_ALERTS'
};

// Ținte și praguri (se pot muta în CONFIG dacă vrei flexibilitate)
var MK_TARGETS = {
  CPL_GREEN: 25,           // CPL în RON considerat excelent
  CPL_YELLOW: 40,          // CPL acceptabil
  CPL_RED_ALERT: 50,       // CPL critic — trimite alert
  CPL_DAYS_CONSECUTIVE: 3, // Zile consecutive de CPL roșu pentru alert
  CTR_GREEN: 5,            // CTR % excelent
  CTR_YELLOW: 3,           // CTR acceptabil
  USERS_GREEN: 500,        // Utilizatori GA4/lună
  USERS_YELLOW: 300,
  GSC_POS_GREEN: 15,       // Poziție medie GSC
  GSC_POS_YELLOW: 22,
  LEADS_TARGET_MONTHLY: 15 // Lead-uri calificate/lună (North Star)
};

// Keywords țintă pentru tracking GSC (update când e cazul)
var MK_TARGET_KEYWORDS = [
  'sistem pontaj electronic',
  'sisteme de pontaj electronic',
  'pontaj electronic',
  'camere supraveghere',
  'camere supraveghere brasov',
  'alarma antiefractie brasov',
  'control acces brasov',
  'detectie incendiu brasov',
  'sistem supraveghere firma'
];


// ═══════════════════════════════════════════════════
// SETUP — rulare unică la instalare
// ═══════════════════════════════════════════════════

/**
 * Creează sheet-urile necesare pentru modulul MARKETING.
 * Rulează MANUAL o singură dată după lipire.
 */
function setupMarketingModule() {
  var ss = SpreadsheetApp.openById(SPREADSHEET_ID);

  // 1. Sheet MARKETING
  var mk = ss.getSheetByName(SH_MK.MARKETING);
  if (!mk) {
    mk = ss.insertSheet(SH_MK.MARKETING);
    mk.getRange(1, 1, 1, 10).setValues([[
      'Data', 'Canal', 'Campanie', 'Impresii', 'Clicuri', 'CTR', 'Cost', 'Conversii', 'CPL', 'Note'
    ]]);
    mk.getRange(1, 1, 1, 10).setFontWeight('bold').setBackground('#D5E8F0');
    mk.setFrozenRows(1);
    Logger.log('✓ Sheet MARKETING creat');
  }

  // 2. Sheet KPI_DASHBOARD
  var kpi = ss.getSheetByName(SH_MK.KPI_DASHBOARD);
  if (!kpi) {
    kpi = ss.insertSheet(SH_MK.KPI_DASHBOARD);
    kpi.getRange(1, 1).setValue('CSSI — KPI Dashboard').setFontSize(16).setFontWeight('bold');
    kpi.getRange(2, 1).setValue('Ultima actualizare: —');
    Logger.log('✓ Sheet KPI_DASHBOARD creat');
  }

  // 3. Sheet MK_ALERTS
  var al = ss.getSheetByName(SH_MK.ALERTS);
  if (!al) {
    al = ss.insertSheet(SH_MK.ALERTS);
    al.getRange(1, 1, 1, 5).setValues([[
      'Timestamp', 'Tip', 'Severitate', 'Mesaj', 'Status'
    ]]);
    al.getRange(1, 1, 1, 5).setFontWeight('bold').setBackground('#FEE2E2');
    al.setFrozenRows(1);
    Logger.log('✓ Sheet MK_ALERTS creat');
  }

  // 4. Adaugă chei API în CONFIG dacă nu există
  var cfg = ss.getSheetByName(SH.CONFIG);
  if (cfg) {
    var existingKeys = cfg.getRange(1, 1, cfg.getLastRow() || 1, 1).getValues().map(function(r){return r[0];});
    var neededKeys = [
      ['GOOGLE_ADS_DEV_TOKEN', ''],
      ['GOOGLE_ADS_CUSTOMER_ID', '666-033-6562'],
      ['GA4_PROPERTY_ID', '525787706'],
      ['GSC_SITE_URL', 'sc-domain:cssi.ro'],
      ['META_GRAPH_TOKEN', ''],
      ['EMAIL_MARKETING_REPORTS', '']
    ];
    neededKeys.forEach(function(kv){
      if (existingKeys.indexOf(kv[0]) === -1) {
        cfg.appendRow(kv);
      }
    });
    Logger.log('✓ CONFIG populat cu chei API placeholder');
  }

  Logger.log('═══ Setup MARKETING complet. Rulează installMarketingTriggers() apoi. ═══');
}


// ═══════════════════════════════════════════════════
// SYNC DATA — populează sheet MARKETING zilnic
// ═══════════════════════════════════════════════════

/**
 * Rulează zilnic la 07:30 — populează sheet MARKETING cu datele de ieri.
 *
 * FAZA 1 (curent): populare pe baza CRM existent + date manuale introduse.
 * FAZA 2 (după obținere Developer Token): integrare Google Ads API + GA4 + GSC.
 *
 * Pentru a activa integrările API, decomentează blocurile `TODO: API`.
 */
function syncMarketingData() {
  try {
    var ss = SpreadsheetApp.openById(SPREADSHEET_ID);
    var mk = ss.getSheetByName(SH_MK.MARKETING);
    if (!mk) { setupMarketingModule(); mk = ss.getSheetByName(SH_MK.MARKETING); }

    var yesterday = new Date();
    yesterday.setDate(yesterday.getDate() - 1);
    var dateStr = Utilities.formatDate(yesterday, Session.getScriptTimeZone(), 'yyyy-MM-dd');

    // === 1. Google Ads (fallback: introducere manuală) ===
    // TODO: API — după obținere GOOGLE_ADS_DEV_TOKEN, înlocuiește cu apel real
    // var adsData = fetchGoogleAdsMetrics(dateStr);
    var adsData = _readManualAdsEntry(dateStr);
    if (adsData) {
      mk.appendRow([
        dateStr, 'Google Ads', adsData.campaign || 'CSSI - Servicii Securitate',
        adsData.impressions, adsData.clicks,
        adsData.clicks > 0 ? (adsData.clicks / adsData.impressions * 100).toFixed(2) + '%' : '0%',
        adsData.cost, adsData.conversions,
        adsData.conversions > 0 ? (adsData.cost / adsData.conversions).toFixed(2) : '—',
        'auto-sync'
      ]);
    }

    // === 2. GA4 — lead-uri din CRM ieri ===
    var leadsYesterday = _countLeadsOnDate(dateStr);
    if (leadsYesterday !== null) {
      mk.appendRow([
        dateStr, 'GA4 (via CRM)', '—',
        '—', '—', '—', 0, leadsYesterday,
        adsData && adsData.cost && leadsYesterday > 0 ? (adsData.cost / leadsYesterday).toFixed(2) : '—',
        leadsYesterday + ' lead-uri noi în CRM'
      ]);
    }

    // === 3. GSC — TODO: după setup OAuth pentru Search Console API ===
    // var gscData = fetchGscTopQueries(dateStr);

    Logger.log('✓ syncMarketingData rulat pentru ' + dateStr);
    return true;
  } catch(e) {
    Logger.log('✗ syncMarketingData eroare: ' + e.message);
    _logAlert('SYNC_ERROR', 'high', 'syncMarketingData a eșuat: ' + e.message);
    return false;
  }
}

/**
 * Helper: numără lead-urile adăugate în CRM la data specifică.
 */
function _countLeadsOnDate(dateStr) {
  try {
    var ss = SpreadsheetApp.openById(SPREADSHEET_ID);
    var crm = ss.getSheetByName(SH.CRM);
    if (!crm) return null;
    var data = crm.getDataRange().getValues();
    var count = 0;
    for (var i = 1; i < data.length; i++) {
      var ts = data[i][0];
      if (!ts) continue;
      var d = (ts instanceof Date) ? ts : new Date(ts);
      if (isNaN(d.getTime())) continue;
      var dStr = Utilities.formatDate(d, Session.getScriptTimeZone(), 'yyyy-MM-dd');
      if (dStr === dateStr) count++;
    }
    return count;
  } catch(e) {
    Logger.log('_countLeadsOnDate err: ' + e.message);
    return null;
  }
}

/**
 * Helper: citește ultima intrare manuală în MARKETING pentru Google Ads,
 * dacă există (util până la conectarea API-ului).
 */
function _readManualAdsEntry(dateStr) {
  // Placeholder: returnează null — MIHAI va introduce manual 1 dată pe zi
  // în MARKETING până e setată Google Ads API.
  // Exemplu entry manuală pe care o poate lăsa:
  // { campaign: 'CSSI - Servicii Securitate', impressions: 54, clicks: 6, cost: 21.20, conversions: 0 }
  return null;
}


// ═══════════════════════════════════════════════════
// KPI DASHBOARD — regenerare zilnică
// ═══════════════════════════════════════════════════

/**
 * Populează sheet KPI_DASHBOARD cu scorecard-ul actual.
 * Colorează celulele Verde/Galben/Roșu conform MK_TARGETS.
 */
function generateKpiDashboard() {
  try {
    var ss = SpreadsheetApp.openById(SPREADSHEET_ID);
    var kpi = ss.getSheetByName(SH_MK.KPI_DASHBOARD);
    if (!kpi) { setupMarketingModule(); kpi = ss.getSheetByName(SH_MK.KPI_DASHBOARD); }

    kpi.clear();
    kpi.getRange(1, 1).setValue('CSSI — KPI Dashboard').setFontSize(16).setFontWeight('bold');
    kpi.getRange(2, 1).setValue('Generat: ' + Utilities.formatDate(new Date(), Session.getScriptTimeZone(), 'yyyy-MM-dd HH:mm'))
      .setFontStyle('italic').setFontColor('#6B7280');

    // Header tabel
    var r = 4;
    kpi.getRange(r, 1, 1, 4).setValues([['KPI', 'Valoare curentă', 'Țintă lună', 'Stare']])
      .setFontWeight('bold').setBackground('#1E40AF').setFontColor('#FFFFFF');
    r++;

    // Calculează metrici pe ultimele 30 zile
    var metrics = _computeCurrentMetrics();

    var rows = [
      ['Lead-uri calificate (30z)', metrics.leadsCount,
       MK_TARGETS.LEADS_TARGET_MONTHLY,
       _statusLeads(metrics.leadsCount)],
      ['CPL mediu (30z)', metrics.cpl === null ? '—' : metrics.cpl + ' RON',
       '≤ ' + MK_TARGETS.CPL_GREEN + ' RON',
       _statusCpl(metrics.cpl)],
      ['CTR Google Ads (7z)', metrics.ctr === null ? '—' : metrics.ctr + '%',
       '≥ ' + MK_TARGETS.CTR_GREEN + '%',
       _statusCtr(metrics.ctr)],
      ['Utilizatori GA4 (30z)', metrics.users === null ? '—' : metrics.users,
       '≥ ' + MK_TARGETS.USERS_GREEN,
       _statusUsers(metrics.users)],
      ['Poziție medie GSC', metrics.gscPos === null ? '—' : metrics.gscPos,
       '≤ ' + MK_TARGETS.GSC_POS_GREEN,
       _statusGscPos(metrics.gscPos)]
    ];

    kpi.getRange(r, 1, rows.length, 4).setValues(rows);

    // Colorare status
    for (var i = 0; i < rows.length; i++) {
      var cellStatus = kpi.getRange(r + i, 4);
      var status = rows[i][3];
      if (status.indexOf('🟢') === 0 || status.toLowerCase().indexOf('verde') >= 0) {
        cellStatus.setBackground('#D1FAE5');
      } else if (status.indexOf('🟡') === 0 || status.toLowerCase().indexOf('galben') >= 0) {
        cellStatus.setBackground('#FEF3C7');
      } else if (status.indexOf('🔴') === 0 || status.toLowerCase().indexOf('ros') >= 0) {
        cellStatus.setBackground('#FEE2E2');
      }
    }

    kpi.autoResizeColumns(1, 4);
    Logger.log('✓ KPI Dashboard regenerat');
    return true;
  } catch(e) {
    Logger.log('✗ generateKpiDashboard: ' + e.message);
    return false;
  }
}

function _computeCurrentMetrics() {
  var m = { leadsCount: 0, cpl: null, ctr: null, users: null, gscPos: null };
  try {
    var ss = SpreadsheetApp.openById(SPREADSHEET_ID);

    // Lead-uri în ultimele 30 zile din CRM
    var crm = ss.getSheetByName(SH.CRM);
    if (crm) {
      var data = crm.getDataRange().getValues();
      var cutoff = new Date(); cutoff.setDate(cutoff.getDate() - 30);
      for (var i = 1; i < data.length; i++) {
        var ts = data[i][0];
        if (!ts) continue;
        var d = (ts instanceof Date) ? ts : new Date(ts);
        if (!isNaN(d.getTime()) && d >= cutoff) m.leadsCount++;
      }
    }

    // Cost + conversii din MARKETING ultimele 30 zile pentru CPL + CTR
    var mk = ss.getSheetByName(SH_MK.MARKETING);
    if (mk) {
      var mkData = mk.getDataRange().getValues();
      var cutoff30 = new Date(); cutoff30.setDate(cutoff30.getDate() - 30);
      var cutoff7 = new Date(); cutoff7.setDate(cutoff7.getDate() - 7);
      var cost30 = 0, conv30 = 0, imp7 = 0, clk7 = 0;
      for (var j = 1; j < mkData.length; j++) {
        var row = mkData[j];
        var rd = (row[0] instanceof Date) ? row[0] : new Date(row[0]);
        if (isNaN(rd.getTime())) continue;
        if (row[1] !== 'Google Ads') continue;
        if (rd >= cutoff30) {
          cost30 += Number(row[6]) || 0;
          conv30 += Number(row[7]) || 0;
        }
        if (rd >= cutoff7) {
          imp7 += Number(row[3]) || 0;
          clk7 += Number(row[4]) || 0;
        }
      }
      if (conv30 > 0) m.cpl = (cost30 / conv30).toFixed(2);
      if (imp7 > 0) m.ctr = ((clk7 / imp7) * 100).toFixed(2);
    }
  } catch(e) { Logger.log('_computeCurrentMetrics: ' + e.message); }
  return m;
}

function _statusLeads(n) {
  if (n >= MK_TARGETS.LEADS_TARGET_MONTHLY) return '🟢 Verde';
  if (n >= MK_TARGETS.LEADS_TARGET_MONTHLY * 0.7) return '🟡 Galben';
  return '🔴 Roșu';
}
function _statusCpl(v) {
  if (v === null) return '— n/a';
  v = Number(v);
  if (v <= MK_TARGETS.CPL_GREEN) return '🟢 Verde';
  if (v <= MK_TARGETS.CPL_YELLOW) return '🟡 Galben';
  return '🔴 Roșu';
}
function _statusCtr(v) {
  if (v === null) return '— n/a';
  v = Number(v);
  if (v >= MK_TARGETS.CTR_GREEN) return '🟢 Verde';
  if (v >= MK_TARGETS.CTR_YELLOW) return '🟡 Galben';
  return '🔴 Roșu';
}
function _statusUsers(n) {
  if (n === null) return '— n/a';
  if (n >= MK_TARGETS.USERS_GREEN) return '🟢 Verde';
  if (n >= MK_TARGETS.USERS_YELLOW) return '🟡 Galben';
  return '🔴 Roșu';
}
function _statusGscPos(v) {
  if (v === null) return '— n/a';
  v = Number(v);
  if (v <= MK_TARGETS.GSC_POS_GREEN) return '🟢 Verde';
  if (v <= MK_TARGETS.GSC_POS_YELLOW) return '🟡 Galben';
  return '🔴 Roșu';
}


// ═══════════════════════════════════════════════════
// RAPORTE AUTOMATE — săptămânal și lunar
// ═══════════════════════════════════════════════════

/**
 * Rulează LUNI 07:30 — trimite raport săptămânal pe email.
 */
function sapRaport() {
  try {
    var email = _getConfig('EMAIL_MARKETING_REPORTS') || EMAIL_ADMIN;
    if (!email) { Logger.log('sapRaport: no email configured'); return; }

    var ss = SpreadsheetApp.openById(SPREADSHEET_ID);
    var m = _computeCurrentMetrics();
    var weekStart = new Date(); weekStart.setDate(weekStart.getDate() - 7);

    // Week-over-week din MARKETING
    var wow = _computeWeekOverWeek();

    var html = '<div style="font-family:Arial,sans-serif;max-width:640px;">';
    html += '<h2 style="color:#1E40AF;">CSSI — Raport săptămânal</h2>';
    html += '<p style="color:#6B7280;font-size:13px;">Perioadă: ' +
      Utilities.formatDate(weekStart, Session.getScriptTimeZone(), 'dd MMM') + ' — ' +
      Utilities.formatDate(new Date(), Session.getScriptTimeZone(), 'dd MMM yyyy') + '</p>';

    html += '<h3>📊 Rezumat</h3>';
    html += '<table style="border-collapse:collapse;width:100%;">' +
      '<tr style="background:#F3F4F6;"><th style="padding:8px;text-align:left;border:1px solid #E5E7EB;">KPI</th>' +
      '<th style="padding:8px;text-align:left;border:1px solid #E5E7EB;">Săptămâna aceasta</th>' +
      '<th style="padding:8px;text-align:left;border:1px solid #E5E7EB;">Δ WoW</th></tr>';

    var kpiRows = [
      ['Lead-uri noi (CRM)', wow.leadsThis, wow.leadsDelta],
      ['Cost Google Ads', wow.costThis + ' RON', wow.costDelta],
      ['Clicuri', wow.clicksThis, wow.clicksDelta],
      ['Conversii', wow.convThis, wow.convDelta]
    ];
    kpiRows.forEach(function(r){
      html += '<tr><td style="padding:8px;border:1px solid #E5E7EB;">' + r[0] + '</td>' +
        '<td style="padding:8px;border:1px solid #E5E7EB;font-weight:bold;">' + r[1] + '</td>' +
        '<td style="padding:8px;border:1px solid #E5E7EB;">' + r[2] + '</td></tr>';
    });
    html += '</table>';

    html += '<h3>🎯 Status KPI lunar</h3>';
    html += '<ul>';
    html += '<li>Lead-uri 30z: <strong>' + m.leadsCount + '</strong> / țintă ' + MK_TARGETS.LEADS_TARGET_MONTHLY + '</li>';
    html += '<li>CPL mediu 30z: <strong>' + (m.cpl === null ? '—' : m.cpl + ' RON') + '</strong></li>';
    html += '<li>CTR 7z: <strong>' + (m.ctr === null ? '—' : m.ctr + '%') + '</strong></li>';
    html += '</ul>';

    html += '<p style="color:#6B7280;font-size:12px;margin-top:24px;">Generat automat de Portal CSSI v3.0 / Modul MARKETING</p>';
    html += '</div>';

    MailApp.sendEmail({
      to: email,
      subject: 'CSSI — Raport săptămânal ' +
        Utilities.formatDate(new Date(), Session.getScriptTimeZone(), 'dd MMM yyyy'),
      htmlBody: html
    });
    Logger.log('✓ sapRaport trimis la ' + email);
  } catch(e) {
    Logger.log('✗ sapRaport: ' + e.message);
  }
}

/**
 * Rulează ziua 1 a lunii la 07:00 — raport lunar complet.
 */
function lunarRaport() {
  try {
    var email = _getConfig('EMAIL_MARKETING_REPORTS') || EMAIL_ADMIN;
    if (!email) return;

    var m = _computeCurrentMetrics();
    var html = '<div style="font-family:Arial,sans-serif;max-width:640px;">';
    html += '<h2 style="color:#1E40AF;">CSSI — Raport lunar</h2>';
    html += '<p style="color:#6B7280;">' +
      Utilities.formatDate(new Date(), Session.getScriptTimeZone(), 'MMMM yyyy') + '</p>';

    html += '<h3>Scorecard lună</h3>';
    html += '<ul>';
    html += '<li>Lead-uri calificate: ' + m.leadsCount + ' / țintă ' + MK_TARGETS.LEADS_TARGET_MONTHLY + ' — ' + _statusLeads(m.leadsCount) + '</li>';
    html += '<li>CPL mediu: ' + (m.cpl === null ? '—' : m.cpl + ' RON') + ' — ' + _statusCpl(m.cpl) + '</li>';
    html += '<li>CTR Ads: ' + (m.ctr === null ? '—' : m.ctr + '%') + ' — ' + _statusCtr(m.ctr) + '</li>';
    html += '</ul>';

    html += '<h3>📝 Recomandări automat generate</h3>';
    html += '<ul>';
    if (m.leadsCount < MK_TARGETS.LEADS_TARGET_MONTHLY) {
      html += '<li>Lead-uri sub țintă: verifică funnel-ul, creșterea bugetului Ads poate să fie necesară</li>';
    }
    if (m.cpl !== null && Number(m.cpl) > MK_TARGETS.CPL_YELLOW) {
      html += '<li>CPL ridicat: adaugă negative keywords, testează noi RSA-uri</li>';
    }
    if (m.ctr !== null && Number(m.ctr) < MK_TARGETS.CTR_YELLOW) {
      html += '<li>CTR scăzut: revizuiește copy-ul reclamelor, testează titluri noi</li>';
    }
    html += '</ul>';

    html += '<p style="color:#6B7280;font-size:12px;margin-top:24px;">Portal CSSI v3.0 / Modul MARKETING</p></div>';

    MailApp.sendEmail({
      to: email,
      subject: 'CSSI — Raport lunar ' + Utilities.formatDate(new Date(), Session.getScriptTimeZone(), 'MMMM yyyy'),
      htmlBody: html
    });
    Logger.log('✓ lunarRaport trimis');
  } catch(e) { Logger.log('✗ lunarRaport: ' + e.message); }
}

function _computeWeekOverWeek() {
  var res = { leadsThis: 0, leadsDelta: '—', costThis: 0, costDelta: '—',
              clicksThis: 0, clicksDelta: '—', convThis: 0, convDelta: '—' };
  try {
    var ss = SpreadsheetApp.openById(SPREADSHEET_ID);
    var now = new Date();
    var wkThis = new Date(now); wkThis.setDate(wkThis.getDate() - 7);
    var wkPrev = new Date(now); wkPrev.setDate(wkPrev.getDate() - 14);

    var crm = ss.getSheetByName(SH.CRM);
    if (crm) {
      var d = crm.getDataRange().getValues();
      var leadsThis = 0, leadsPrev = 0;
      for (var i = 1; i < d.length; i++) {
        var ts = d[i][0]; if (!ts) continue;
        var dt = (ts instanceof Date) ? ts : new Date(ts);
        if (isNaN(dt.getTime())) continue;
        if (dt >= wkThis) leadsThis++;
        else if (dt >= wkPrev) leadsPrev++;
      }
      res.leadsThis = leadsThis;
      res.leadsDelta = _pct(leadsThis, leadsPrev);
    }

    var mk = ss.getSheetByName(SH_MK.MARKETING);
    if (mk) {
      var md = mk.getDataRange().getValues();
      var costThis = 0, costPrev = 0, clicksThis = 0, clicksPrev = 0, convThis = 0, convPrev = 0;
      for (var j = 1; j < md.length; j++) {
        var row = md[j];
        if (row[1] !== 'Google Ads') continue;
        var rd = (row[0] instanceof Date) ? row[0] : new Date(row[0]);
        if (isNaN(rd.getTime())) continue;
        var c = Number(row[6]) || 0, k = Number(row[4]) || 0, cv = Number(row[7]) || 0;
        if (rd >= wkThis) { costThis += c; clicksThis += k; convThis += cv; }
        else if (rd >= wkPrev) { costPrev += c; clicksPrev += k; convPrev += cv; }
      }
      res.costThis = costThis.toFixed(2);
      res.costDelta = _pct(costThis, costPrev);
      res.clicksThis = clicksThis;
      res.clicksDelta = _pct(clicksThis, clicksPrev);
      res.convThis = convThis;
      res.convDelta = _pct(convThis, convPrev);
    }
  } catch(e) { Logger.log('_computeWeekOverWeek: ' + e.message); }
  return res;
}

function _pct(now, prev) {
  if (prev === 0 && now === 0) return '0%';
  if (prev === 0) return '+∞%';
  var d = ((now - prev) / prev) * 100;
  return (d >= 0 ? '+' : '') + d.toFixed(1) + '%';
}


// ═══════════════════════════════════════════════════
// ALERTE — CPL și rank drop
// ═══════════════════════════════════════════════════

/**
 * Rulează zilnic 11:00 — alertă dacă CPL > 50 RON timp de 3 zile consecutive.
 */
function checkCPL() {
  try {
    var ss = SpreadsheetApp.openById(SPREADSHEET_ID);
    var mk = ss.getSheetByName(SH_MK.MARKETING);
    if (!mk) return;
    var data = mk.getDataRange().getValues();
    if (data.length < 2) return;

    // Grupează CPL pe zile (ultimele 5 zile Google Ads)
    var byDate = {};
    for (var i = 1; i < data.length; i++) {
      var row = data[i];
      if (row[1] !== 'Google Ads') continue;
      var rd = (row[0] instanceof Date) ? row[0] : new Date(row[0]);
      if (isNaN(rd.getTime())) continue;
      var ds = Utilities.formatDate(rd, Session.getScriptTimeZone(), 'yyyy-MM-dd');
      var cost = Number(row[6]) || 0, conv = Number(row[7]) || 0;
      if (!byDate[ds]) byDate[ds] = { cost: 0, conv: 0 };
      byDate[ds].cost += cost;
      byDate[ds].conv += conv;
    }

    // Iau ultimele N zile
    var days = Object.keys(byDate).sort().reverse().slice(0, MK_TARGETS.CPL_DAYS_CONSECUTIVE);
    if (days.length < MK_TARGETS.CPL_DAYS_CONSECUTIVE) return; // insuficiente date

    var allOverLimit = days.every(function(d){
      var x = byDate[d];
      if (x.conv === 0) return x.cost > MK_TARGETS.CPL_RED_ALERT; // costuri fără conversii = overLimit
      return (x.cost / x.conv) > MK_TARGETS.CPL_RED_ALERT;
    });

    if (allOverLimit) {
      var email = _getConfig('EMAIL_MARKETING_REPORTS') || EMAIL_ADMIN;
      var msg = 'CPL > ' + MK_TARGETS.CPL_RED_ALERT + ' RON în ultimele ' +
        MK_TARGETS.CPL_DAYS_CONSECUTIVE + ' zile consecutive. Verifică campania.';
      _logAlert('CPL_HIGH', 'high', msg);
      if (email) {
        MailApp.sendEmail({ to: email, subject: '🔴 CSSI Alert — CPL ridicat', body: msg });
      }
    }
  } catch(e) { Logger.log('checkCPL: ' + e.message); }
}

/**
 * Rulează zilnic 11:00 — detectează poziții GSC care scad > 5 locuri.
 * TODO: necesită Search Console API; pentru MVP loghează doar când e posibil manual.
 */
function checkRankDrop() {
  // TODO: integrare GSC API după setup OAuth
  Logger.log('checkRankDrop: placeholder — necesită GSC API setup');
}

/**
 * Rulează vineri 10:00 — sugestii keywords pe baza top queries GSC.
 * TODO: GSC API.
 */
function keywordIdeas() {
  Logger.log('keywordIdeas: placeholder — necesită GSC API setup');
}


// ═══════════════════════════════════════════════════
// HELPERS
// ═══════════════════════════════════════════════════

function _getConfig(key) {
  try {
    var ss = SpreadsheetApp.openById(SPREADSHEET_ID);
    var cfg = ss.getSheetByName(SH.CONFIG);
    if (!cfg) return null;
    var d = cfg.getDataRange().getValues();
    for (var i = 0; i < d.length; i++) {
      if (d[i][0] === key) return d[i][1];
    }
    return null;
  } catch(e) { return null; }
}

function _logAlert(type, severity, message) {
  try {
    var ss = SpreadsheetApp.openById(SPREADSHEET_ID);
    var al = ss.getSheetByName(SH_MK.ALERTS);
    if (!al) return;
    al.appendRow([new Date(), type, severity, message, 'open']);
  } catch(e) { Logger.log('_logAlert: ' + e.message); }
}


// ═══════════════════════════════════════════════════
// INSTALARE TRIGGERE — rulare unică
// ═══════════════════════════════════════════════════

/**
 * Creează trigger-ele necesare pentru modulul MARKETING.
 * Rulează MANUAL o singură dată după setupMarketingModule().
 */
function installMarketingTriggers() {
  // Șterge triggere vechi ale modulului
  var fns = ['syncMarketingData', 'generateKpiDashboard', 'sapRaport',
             'lunarRaport', 'checkCPL', 'checkRankDrop', 'keywordIdeas'];
  var existing = ScriptApp.getProjectTriggers();
  existing.forEach(function(t){
    if (fns.indexOf(t.getHandlerFunction()) >= 0) ScriptApp.deleteTrigger(t);
  });

  // syncMarketingData — zilnic 07:30
  ScriptApp.newTrigger('syncMarketingData').timeBased().everyDays(1).atHour(7).nearMinute(30).create();
  // generateKpiDashboard — zilnic 07:45
  ScriptApp.newTrigger('generateKpiDashboard').timeBased().everyDays(1).atHour(7).nearMinute(45).create();
  // sapRaport — luni 07:30
  ScriptApp.newTrigger('sapRaport').timeBased().onWeekDay(ScriptApp.WeekDay.MONDAY).atHour(7).nearMinute(30).create();
  // lunarRaport — ziua 1 a lunii 07:00
  ScriptApp.newTrigger('lunarRaport').timeBased().onMonthDay(1).atHour(7).create();
  // checkCPL — zilnic 11:00
  ScriptApp.newTrigger('checkCPL').timeBased().everyDays(1).atHour(11).create();
  // checkRankDrop — zilnic 11:15
  ScriptApp.newTrigger('checkRankDrop').timeBased().everyDays(1).atHour(11).nearMinute(15).create();
  // keywordIdeas — vineri 10:00
  ScriptApp.newTrigger('keywordIdeas').timeBased().onWeekDay(ScriptApp.WeekDay.FRIDAY).atHour(10).create();

  Logger.log('✓ Triggere MARKETING instalate (7 funcții)');
}

/**
 * Test manual: rulează toate funcțiile o dată pentru sanity-check.
 */
function testMarketingModule() {
  Logger.log('--- Test modul MARKETING ---');
  setupMarketingModule();
  syncMarketingData();
  generateKpiDashboard();
  sapRaport();
  checkCPL();
  Logger.log('--- Test complet ---');
}
