/**
 * ═══════════════════════════════════════════════════════════════
 * CSSI APPS SCRIPT v3.0 — Sistem Integrat de Management
 * ═══════════════════════════════════════════════════════════════
 * 
 * Backend pentru Portal CSSI 3.0
 * Conectează toate modulele prin Sheet-ul central PROIECTE
 * 
 * SETUP:
 * 1. Creează un Google Spreadsheet nou cu sheet-urile definite mai jos
 * 2. Extensions > Apps Script > Lipește acest cod
 * 3. Deploy > Web app > Execute as "Me" > Access "Anyone"
 * 4. Copiază URL-ul deployment-ului în admin.html (variabila API_URL)
 * 5. Setează triggere: zilnicRaport la 08:00, verificaMentenanta la 09:00
 * 
 * SHEET-URI NECESARE (creează manual cu headerele de mai jos):
 * 
 * 1. PROIECTE: ID | Client | Telefon | Email | Serviciu | Locatie | Valoare | Status | Echipa | DataStart | DataFin | Note | CreatedAt
 * 2. CRM: Timestamp | Nume | Telefon | Email | Serviciu | Sursa | Status | ProiectID | Valoare | Locatie | Note
 * 3. EXECUTIE: Data | ProiectID | Echipa | Ore | Muncitori | Stadiu | Observatii | Materiale
 * 4. MENTENANTA: Client | ProiectID | TipContract | Frecventa | UltimaVerificare | UrmatoareaVerificare | Status | Valoare | Telefon | Echipa | Note | History (JSON)
 * 5. PROIECTARE: ProiectID | TipAviz | Status | Deadline | Responsabil | LinkDosar | Note | Checks (JSON)
 * 6. MATERIALE: Data | ProiectID | Material | Cantitate | Urgenta | Cost | Status | Solicitant | Furnizor | Note
 * 6b. INVENTAR: Nume | Cod | Categorie | Stoc | Unitate | Minim | Pret | Furnizor | Locatie | Note | History (JSON)
 * 7. FINANCIAR: ProiectID | NrFactura | DataFactura | Scadenta | Client | Suma | TVA | Total | Incasat | Restant | Status | Note | Payments (JSON)
 * 8. SOCIAL: Data | Platforma | Continut | Status | Tip | Note
 * 9. PLANIFICARE: Data | Ora | Descriere | ProiectID | Echipa | Tip | Durata | Prioritate | Status | Note
 * 10. CONFIG: Key | Value (pentru setări: email_admin, email_notificari, etc.)
 */

// ═══════ CONFIGURATION ═══════
var SPREADSHEET_ID = ''; // ← PUNE ID-ul spreadsheet-ului tău aici
var EMAIL_ADMIN = ''; // ← Email-ul tău pentru rapoarte și alerte

// Sheet names
var SH = {
  PROIECTE: 'PROIECTE',
  CRM: 'CRM',
  EXECUTIE: 'EXECUTIE',
  MENTENANTA: 'MENTENANTA',
  PROIECTARE: 'PROIECTARE',
  MATERIALE: 'MATERIALE',
  INVENTAR: 'INVENTAR',
  FINANCIAR: 'FINANCIAR',
  SOCIAL: 'SOCIAL',
  PLANIFICARE: 'PLANIFICARE',
  CONFIG: 'CONFIG'
};

// Status values
var STATUSES = ['Lead', 'Ofertă', 'Contract', 'Proiectare', 'Execuție', 'Recepție', 'Facturat', 'Mentenanță'];


// ═══════════════════════════════════════════════════
// API ENDPOINT (doGet) — Portal citește date de aici
// ═══════════════════════════════════════════════════

function doGet(e) {
  var action = e.parameter.action || 'getAll';
  var result = {};

  try {
    switch (action) {
      case 'getAll':
        result = getAllData();
        break;
      case 'getProjects':
        result = getProjects();
        break;
      case 'getKPI':
        result = getKPI();
        break;
      case 'getMentenanta':
        result = getMentenanta();
        break;
      case 'getFinanciar':
        result = getFinanciar();
        break;
      case 'getCRM':
        result = getCRM();
        break;
      case 'getMateriale':
        result = getMaterialeData();
        break;
      case 'getInventar':
        result = getInventarData();
        break;
      case 'getSocial':
        result = getSocialData();
        break;
      case 'getPlanificare':
        result = getPlanificare();
        break;
      case 'getExecutie':
        result = getExecutieData();
        break;
      case 'getProiectare':
        result = getProiectareData();
        break;
      case 'getAlerts':
        result = getAlerts();
        break;
      default:
        result = {error: 'Unknown action: ' + action};
    }
  } catch (err) {
    result = {error: err.toString()};
  }

  return ContentService.createTextOutput(JSON.stringify(result))
    .setMimeType(ContentService.MimeType.JSON);
}


// ═══════════════════════════════════════════════════
// API ENDPOINT (doPost) — Portal trimite date aici
// ═══════════════════════════════════════════════════

function doPost(e) {
  var data = JSON.parse(e.postData.contents);
  var action = data.action || '';
  var result = {};

  try {
    switch (action) {
      case 'addProject':
        result = addProject(data);
        break;
      case 'updateStatus':
        result = updateProjectStatus(data.id, data.status);
        break;
      case 'addMaterial':
        result = addMaterial(data);
        break;
      case 'updateMaterialeStatus':
        result = updateMaterialeStatus(data.row, data.status);
        break;
      case 'addInventar':
        result = addInventarItem(data.item || data);
        break;
      case 'updateInventar':
        result = updateInventarItem(data.row, data.item || data);
        break;
      case 'updateInventarStock':
        result = updateInventarStock(data.row, data.stoc, data.history);
        break;
      case 'addSocial':
        result = addSocialPost(data.post || data);
        break;
      case 'updateSocialStatus':
        result = updateSocialStatus(data.row, data.status);
        break;
      case 'updateSocial':
        result = updateSocialPost(data.row, data.post || data);
        break;
      case 'addExecutie':
        result = addExecutie(data.report || data);
        break;
      case 'updateExecutie':
        result = updateExecutieReport(data.row, data.report);
        break;
      case 'addFactura':
        result = addFactura(data);
        break;
      case 'updateFinanciar':
        result = updateFinanciarPayment(data.row, data.incasat, data.restant, data.status, data.payments);
        break;
      case 'updateFinanciarFull':
        result = updateFinanciarFull(data.row, data.invoice || data);
        break;
      case 'addMentenanta':
        result = addMentenanta(data);
        break;
      case 'updateMentenantaStatus':
        result = updateMentenantaStatus(data.row, data.status);
        break;
      case 'updateMentenanta':
        result = updateMentenantaFull(data.row, data.contract || data);
        break;
      case 'markMentenantaDone':
        result = markMentenantaDone(data.row, data.ultima, data.urmatoarea, data.history);
        break;
      case 'crmToProject':
        result = convertCrmToProject(data.crmRow);
        break;
      case 'addCRM':
        result = addCRM(data.lead);
        break;
      case 'updateCRMStatus':
        result = updateCRMStatus(data.row, data.status);
        break;
      case 'addPlanificare':
        result = addPlanificareTask(data.task);
        break;
      case 'updatePlanificare':
        result = updatePlanificareStatus(data.row, data.status);
        break;
      case 'updatePlanificareTask':
        result = updatePlanificareTask(data.row, data.task);
        break;
      case 'addProiectare':
        result = addProiectareDosar(data.dosar || data);
        break;
      case 'updateProiectare':
        result = updateProiectareChecks(data.row, data.status, data.checks);
        break;
      case 'updateProiectareStatus':
        result = updateProiectareStatusOnly(data.row, data.status);
        break;
      case 'updateProiectareDosar':
        result = updateProiectareFullDosar(data.row, data.dosar);
        break;
      default:
        result = {error: 'Unknown action: ' + action};
    }
  } catch (err) {
    result = {error: err.toString()};
  }

  return ContentService.createTextOutput(JSON.stringify(result))
    .setMimeType(ContentService.MimeType.JSON);
}


// ═══════════════════════════════════════════════════
// GET FUNCTIONS
// ═══════════════════════════════════════════════════

function getAllData() {
  return {
    projects: getProjects(),
    kpi: getKPI(),
    alerts: getAlerts(),
    timestamp: new Date().toISOString()
  };
}

function getProjects() {
  var sheet = getSheet(SH.PROIECTE);
  var data = sheet.getDataRange().getValues();
  if (data.length <= 1) return [];
  
  var headers = data[0];
  var projects = [];
  
  for (var i = 1; i < data.length; i++) {
    var row = data[i];
    if (!row[0]) continue; // Skip empty rows
    projects.push({
      id: row[0],
      client: row[1],
      telefon: row[2],
      email: row[3],
      serviciu: row[4],
      loc: row[5],
      valoare: Number(row[6]) || 0,
      status: row[7],
      echipa: row[8],
      dataStart: row[9] ? Utilities.formatDate(new Date(row[9]), 'Europe/Bucharest', 'yyyy-MM-dd') : '',
      dataFin: row[10] ? Utilities.formatDate(new Date(row[10]), 'Europe/Bucharest', 'yyyy-MM-dd') : '',
      note: row[11]
    });
  }
  
  return projects;
}

function getKPI() {
  var projects = getProjects();
  var financiar = getFinanciar();
  var mentenanta = getMentenanta();
  
  var activeStatuses = ['Lead', 'Ofertă', 'Contract', 'Proiectare', 'Execuție', 'Recepție'];
  var active = projects.filter(function(p) { return activeStatuses.indexOf(p.status) >= 0; });
  var totalVal = projects.reduce(function(s, p) { return s + p.valoare; }, 0);
  
  // Facturi restante
  var restante = financiar.filter(function(f) { return f.status === 'Restantă' || (f.restant > 0); });
  var valRestante = restante.reduce(function(s, f) { return s + (f.restant || 0); }, 0);
  
  // Mentenanțe scadente (următoarele 30 zile)
  var now = new Date();
  var in30 = new Date(now.getTime() + 30 * 24 * 60 * 60 * 1000);
  var scadente = mentenanta.filter(function(m) {
    if (!m.urmatoarea) return false;
    var d = new Date(m.urmatoarea);
    return d >= now && d <= in30;
  });
  
  return {
    proiecteActive: active.length,
    proiecteTotal: projects.length,
    valoarePipeline: totalVal,
    facturiRestante: restante.length,
    valoareRestante: valRestante,
    mentenanteScadente: scadente.length,
    leaduri: projects.filter(function(p) { return p.status === 'Lead'; }).length,
    executie: projects.filter(function(p) { return p.status === 'Execuție'; }).length
  };
}

function getMentenanta() {
  var sheet = getSheet(SH.MENTENANTA);
  var data = sheet.getDataRange().getValues();
  if (data.length <= 1) return [];
  
  var result = [];
  for (var i = 1; i < data.length; i++) {
    var row = data[i];
    if (!row[0]) continue;
    result.push({
      row: i,
      client: row[0],
      proiectId: row[1] || '',
      tip: row[2] || 'Standard',
      frecventa: row[3] || 'Trimestrial',
      ultima: row[4] ? Utilities.formatDate(new Date(row[4]), 'Europe/Bucharest', 'yyyy-MM-dd') : '',
      urmatoarea: row[5] ? Utilities.formatDate(new Date(row[5]), 'Europe/Bucharest', 'yyyy-MM-dd') : '',
      status: row[6] || 'Activ',
      valoare: Number(row[7]) || 0,
      telefon: row[8] || '',
      echipa: row[9] || '',
      note: row[10] || '',
      history: row[11] || '[]'
    });
  }
  return result;
}

function getFinanciar() {
  var sheet = getSheet(SH.FINANCIAR);
  var data = sheet.getDataRange().getValues();
  if (data.length <= 1) return [];
  
  var result = [];
  for (var i = 1; i < data.length; i++) {
    var row = data[i];
    if (!row[0]) continue;
    result.push({
      row: i,
      proiectId: row[0],
      nrFactura: row[1] || '',
      data: row[2] ? Utilities.formatDate(new Date(row[2]), 'Europe/Bucharest', 'yyyy-MM-dd') : '',
      scadenta: row[3] ? Utilities.formatDate(new Date(row[3]), 'Europe/Bucharest', 'yyyy-MM-dd') : '',
      client: row[4] || '',
      suma: Number(row[5]) || 0,
      tva: Number(row[6]) || 0,
      total: Number(row[7]) || 0,
      incasat: Number(row[8]) || 0,
      restant: Number(row[9]) || 0,
      status: row[10] || 'Emisă',
      note: row[11] || '',
      payments: row[12] || '[]'
    });
  }
  return result;
}

function getAlerts() {
  var alerts = [];
  var now = new Date();
  
  // 1. Mentenanțe scadente în 14 zile
  var mentenanta = getMentenanta();
  var in14 = new Date(now.getTime() + 14 * 24 * 60 * 60 * 1000);
  mentenanta.forEach(function(m) {
    if (!m.urmatoarea) return;
    var d = new Date(m.urmatoarea);
    if (d >= now && d <= in14) {
      var zile = Math.ceil((d - now) / (24 * 60 * 60 * 1000));
      alerts.push({
        text: 'Mentenanță scadentă: ' + m.client + ' — ' + m.tipContract + ' în ' + zile + ' zile',
        color: zile <= 3 ? 'var(--red)' : 'var(--orange)',
        time: zile <= 1 ? 'Mâine' : 'În ' + zile + ' zile',
        priority: 1
      });
    }
  });
  
  // 2. Facturi restante
  var financiar = getFinanciar();
  financiar.forEach(function(f) {
    if (f.restant > 0) {
      alerts.push({
        text: 'Factură restantă: ' + f.proiectId + ' — ' + f.restant.toLocaleString() + ' RON',
        color: 'var(--red)',
        time: 'Urgent',
        priority: 0
      });
    }
  });
  
  // 3. Lead-uri noi (ultimele 7 zile)
  var projects = getProjects();
  var in7ago = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);
  projects.forEach(function(p) {
    if (p.status === 'Lead' && p.dataStart) {
      var d = new Date(p.dataStart);
      if (d >= in7ago) {
        alerts.push({
          text: 'Lead nou: ' + p.client + ' — ' + p.serviciu,
          color: 'var(--blue)',
          time: 'Recent',
          priority: 2
        });
      }
    }
  });
  
  // Sort by priority
  alerts.sort(function(a, b) { return a.priority - b.priority; });
  return alerts.slice(0, 10); // Max 10 alerts
}


// ═══════════════════════════════════════════════════
// WRITE FUNCTIONS
// ═══════════════════════════════════════════════════

/**
 * Adaugă proiect nou cu auto-generare ID
 */
function addProject(data) {
  var sheet = getSheet(SH.PROIECTE);
  var newId = generateProjectId();
  
  sheet.appendRow([
    newId,
    data.client || '',
    data.telefon || '',
    data.email || '',
    data.serviciu || '',
    data.locatie || 'Brașov',
    Number(data.valoare) || 0,
    data.status || 'Lead',
    data.echipa || '',
    data.dataStart || new Date(),
    '', // dataFin
    data.note || '',
    new Date() // createdAt
  ]);
  
  // Notificare admin
  if (EMAIL_ADMIN) {
    sendEmail(
      EMAIL_ADMIN,
      '📋 Proiect Nou: ' + newId + ' — ' + data.client,
      'Proiect nou adăugat în Portal CSSI:\n\n' +
      'ID: ' + newId + '\n' +
      'Client: ' + data.client + '\n' +
      'Serviciu: ' + data.serviciu + '\n' +
      'Valoare: ' + data.valoare + ' RON\n' +
      'Status: ' + data.status + '\n\n' +
      'Verifică în portal: [link portal]'
    );
  }
  
  return {success: true, id: newId};
}

/**
 * Actualizează statusul unui proiect (manual — key feature!)
 */
function updateProjectStatus(projectId, newStatus) {
  var sheet = getSheet(SH.PROIECTE);
  var data = sheet.getDataRange().getValues();
  
  for (var i = 1; i < data.length; i++) {
    if (data[i][0] === projectId) {
      sheet.getRange(i + 1, 8).setValue(newStatus); // Column H = Status
      
      // Dacă statusul e "Mentenanță", creează automat un contract de mentenanță
      if (newStatus === 'Mentenanță') {
        autoCreateMentenanta(data[i]);
      }
      
      // Notificare la schimbare status
      if (EMAIL_ADMIN) {
        sendEmail(
          EMAIL_ADMIN,
          '🔄 ' + projectId + ' → ' + newStatus,
          'Statusul proiectului ' + projectId + ' (' + data[i][1] + ') a fost schimbat la: ' + newStatus
        );
      }
      
      return {success: true, id: projectId, status: newStatus};
    }
  }
  
  return {error: 'Project not found: ' + projectId};
}

/**
 * Adaugă solicitare de materiale legată de un proiect
 */
function addMaterial(data) {
  var sheet = getSheet(SH.MATERIALE);
  var rowNum = sheet.getLastRow() + 1;
  var r = data.request || data;
  sheet.appendRow([
    r.data || new Date(),
    r.pid || r.proiectId || '',
    r.material || '',
    Number(r.cant || r.cantitate) || 1,
    r.urg || r.urgenta || 'Normal',
    Number(r.cost) || 0,
    r.status || 'Solicitat',
    r.sol || r.solicitant || '',
    r.furnizor || '',
    r.note || ''
  ]);
  return {success: true, row: rowNum - 1};
}

/**
 * Citește toate solicitările de materiale
 */
function getMaterialeData() {
  var sheet = getSheet(SH.MATERIALE);
  var data = sheet.getDataRange().getValues();
  if (data.length <= 1) return [];
  var result = [];
  for (var i = 1; i < data.length; i++) {
    var row = data[i];
    if (!row[2]) continue; // skip empty material
    result.push({
      row: i,
      data: row[0] ? Utilities.formatDate(new Date(row[0]), 'Europe/Bucharest', 'yyyy-MM-dd') : '',
      proiectId: row[1] || '',
      material: row[2] || '',
      cantitate: Number(row[3]) || 1,
      urgenta: row[4] || 'Normal',
      cost: Number(row[5]) || 0,
      status: row[6] || 'Solicitat',
      solicitant: row[7] || '',
      furnizor: row[8] || '',
      note: row[9] || ''
    });
  }
  return result;
}

/**
 * Actualizează statusul unei solicitări de materiale
 */
function updateMaterialeStatus(mRow, newStatus) {
  var sheet = getSheet(SH.MATERIALE);
  if (mRow < 1) return {error: 'Invalid row'};
  sheet.getRange(mRow + 1, 7).setValue(newStatus);
  return {success: true, row: mRow, status: newStatus};
}

/**
 * Citește inventarul complet
 */
function getInventarData() {
  var sheet = getSheet(SH.INVENTAR);
  var data = sheet.getDataRange().getValues();
  if (data.length <= 1) return [];
  var result = [];
  for (var i = 1; i < data.length; i++) {
    var row = data[i];
    if (!row[0]) continue;
    result.push({
      row: i,
      nume: row[0] || '',
      cod: row[1] || '',
      categorie: row[2] || 'Altele',
      stoc: Number(row[3]) || 0,
      unitate: row[4] || 'buc',
      minim: Number(row[5]) || 0,
      pret: Number(row[6]) || 0,
      furnizor: row[7] || '',
      locatie: row[8] || '',
      note: row[9] || '',
      history: row[10] || '[]'
    });
  }
  return result;
}

/**
 * Adaugă produs nou în inventar
 */
function addInventarItem(item) {
  var sheet = getSheet(SH.INVENTAR);
  var rowNum = sheet.getLastRow() + 1;
  sheet.appendRow([
    item.nume || '',
    item.cod || '',
    item.cat || item.categorie || 'Altele',
    Number(item.stoc) || 0,
    item.unit || item.unitate || 'buc',
    Number(item.minim) || 0,
    Number(item.pret) || 0,
    item.furnizor || '',
    item.locatie || '',
    item.note || '',
    '[]'
  ]);
  return {success: true, row: rowNum - 1};
}

/**
 * Actualizează produs în inventar (editare completă)
 */
function updateInventarItem(iRow, item) {
  var sheet = getSheet(SH.INVENTAR);
  if (iRow < 1) return {error: 'Invalid row'};
  var r = iRow + 1;
  sheet.getRange(r, 1).setValue(item.nume || '');
  sheet.getRange(r, 2).setValue(item.cod || '');
  sheet.getRange(r, 3).setValue(item.cat || item.categorie || 'Altele');
  sheet.getRange(r, 4).setValue(Number(item.stoc) || 0);
  sheet.getRange(r, 5).setValue(item.unit || item.unitate || 'buc');
  sheet.getRange(r, 6).setValue(Number(item.minim) || 0);
  sheet.getRange(r, 7).setValue(Number(item.pret) || 0);
  sheet.getRange(r, 8).setValue(item.furnizor || '');
  sheet.getRange(r, 9).setValue(item.locatie || '');
  sheet.getRange(r, 10).setValue(item.note || '');
  return {success: true, row: iRow};
}

/**
 * Actualizează stoc + istoric mișcări
 */
function updateInventarStock(iRow, newStoc, historyJson) {
  var sheet = getSheet(SH.INVENTAR);
  if (iRow < 1) return {error: 'Invalid row'};
  var r = iRow + 1;
  sheet.getRange(r, 4).setValue(Number(newStoc) || 0);
  sheet.getRange(r, 11).setValue(historyJson || '[]');
  return {success: true, row: iRow};
}

/**
 * Citește toate postările social media
 */
function getSocialData() {
  var sheet = getSheet(SH.SOCIAL);
  var data = sheet.getDataRange().getValues();
  if (data.length <= 1) return [];
  var result = [];
  for (var i = 1; i < data.length; i++) {
    var row = data[i];
    if (!row[2]) continue;
    result.push({
      row: i,
      data: row[0] ? Utilities.formatDate(new Date(row[0]), 'Europe/Bucharest', 'yyyy-MM-dd') : '',
      platforma: row[1] || 'fb',
      continut: row[2] || '',
      status: row[3] || 'Idee',
      tip: row[4] || 'Foto',
      note: row[5] || ''
    });
  }
  return result;
}

/**
 * Adaugă postare social media
 */
function addSocialPost(post) {
  var sheet = getSheet(SH.SOCIAL);
  var rowNum = sheet.getLastRow() + 1;
  sheet.appendRow([
    post.data || new Date(),
    post.plat || post.platforma || 'fb',
    post.content || post.continut || '',
    post.status || 'Draft',
    post.tip || 'Foto',
    post.note || ''
  ]);
  return {success: true, row: rowNum - 1};
}

/**
 * Actualizează status postare social
 */
function updateSocialStatus(sRow, newStatus) {
  var sheet = getSheet(SH.SOCIAL);
  if (sRow < 1) return {error: 'Invalid row'};
  sheet.getRange(sRow + 1, 4).setValue(newStatus);
  return {success: true, row: sRow, status: newStatus};
}

/**
 * Actualizare completă postare social
 */
function updateSocialPost(sRow, post) {
  var sheet = getSheet(SH.SOCIAL);
  if (sRow < 1) return {error: 'Invalid row'};
  var r = sRow + 1;
  sheet.getRange(r, 1).setValue(post.data || '');
  sheet.getRange(r, 2).setValue(post.plat || post.platforma || 'fb');
  sheet.getRange(r, 3).setValue(post.content || post.continut || '');
  sheet.getRange(r, 4).setValue(post.status || 'Draft');
  sheet.getRange(r, 5).setValue(post.tip || 'Foto');
  sheet.getRange(r, 6).setValue(post.note || '');
  return {success: true, row: sRow};
}
 * Citește toate rapoartele din EXECUTIE
 */
function getExecutieData() {
  var sheet = getSheet(SH.EXECUTIE);
  var data = sheet.getDataRange().getValues();
  if (data.length <= 1) return [];
  
  var result = [];
  for (var i = 1; i < data.length; i++) {
    var row = data[i];
    if (!row[1]) continue; // Skip rows fără ProiectID
    result.push({
      row: i,
      data: row[0] ? Utilities.formatDate(new Date(row[0]), 'Europe/Bucharest', 'yyyy-MM-dd') : '',
      proiectId: row[1],
      echipa: row[2] || '',
      ore: Number(row[3]) || 0,
      muncitori: Number(row[4]) || 1,
      stadiu: Number(row[5]) || 0,
      observatii: row[6] || '',
      materiale: row[7] || ''
    });
  }
  return result;
}

/**
 * Adaugă raport de execuție legat de un proiect
 */
function addExecutie(data) {
  var sheet = getSheet(SH.EXECUTIE);
  var rowNum = sheet.getLastRow() + 1;
  sheet.appendRow([
    data.data || new Date(),
    data.proiectId || '',
    data.echipa || '',
    Number(data.ore) || 0,
    Number(data.muncitori) || 1,
    Number(data.stadiu) || 0,
    data.obs || data.observatii || '',
    data.materiale || ''
  ]);
  return {success: true, row: rowNum - 1};
}

/**
 * Actualizează un raport de execuție existent
 */
function updateExecutieReport(execRow, report) {
  var sheet = getSheet(SH.EXECUTIE);
  if (execRow < 1) return {error: 'Invalid row'};
  var r = execRow + 1;
  sheet.getRange(r, 1).setValue(report.data || '');
  sheet.getRange(r, 2).setValue(report.proiectId || '');
  sheet.getRange(r, 3).setValue(report.echipa || '');
  sheet.getRange(r, 4).setValue(Number(report.ore) || 0);
  sheet.getRange(r, 5).setValue(Number(report.muncitori) || 1);
  sheet.getRange(r, 6).setValue(Number(report.stadiu) || 0);
  sheet.getRange(r, 7).setValue(report.obs || '');
  sheet.getRange(r, 8).setValue(report.materiale || '');
  return {success: true, row: execRow};
}

/**
 * Adaugă factură legată de un proiect
 */
function addFactura(data) {
  var sheet = getSheet(SH.FINANCIAR);
  var rowNum = sheet.getLastRow() + 1;
  var inv = data.invoice || data;
  var suma = Number(inv.suma) || 0;
  var tva = Number(inv.tva) || suma * 0.19;
  var total = Number(inv.total) || suma + tva;
  var incasat = Number(inv.incasat) || 0;
  var restant = Number(inv.restant) || Math.max(0, total - incasat);
  var status = incasat >= total ? 'Încasată' : incasat > 0 ? 'Parțial Încasată' : 'Emisă';
  
  sheet.appendRow([
    inv.pid || inv.proiectId || '',
    inv.nr || inv.nrFactura || '',
    inv.data || inv.dataFactura || new Date(),
    inv.scadenta || '',
    inv.client || '',
    suma, tva, total,
    incasat, restant,
    status,
    inv.note || '',
    '[]'
  ]);
  return {success: true, row: rowNum - 1};
}

/**
 * Actualizează plata pe o factură (încasare parțială/totală)
 */
function updateFinanciarPayment(fRow, incasat, restant, status, paymentsJson) {
  var sheet = getSheet(SH.FINANCIAR);
  if (fRow < 1) return {error: 'Invalid row'};
  var r = fRow + 1;
  sheet.getRange(r, 9).setValue(Number(incasat) || 0);
  sheet.getRange(r, 10).setValue(Number(restant) || 0);
  sheet.getRange(r, 11).setValue(status || 'Emisă');
  sheet.getRange(r, 13).setValue(paymentsJson || '[]');
  return {success: true, row: fRow};
}

/**
 * Actualizează complet o factură
 */
function updateFinanciarFull(fRow, invoice) {
  var sheet = getSheet(SH.FINANCIAR);
  if (fRow < 1) return {error: 'Invalid row'};
  var r = fRow + 1;
  var inv = invoice;
  sheet.getRange(r, 1).setValue(inv.pid || '');
  sheet.getRange(r, 2).setValue(inv.nr || '');
  sheet.getRange(r, 3).setValue(inv.data || '');
  sheet.getRange(r, 4).setValue(inv.scadenta || '');
  sheet.getRange(r, 5).setValue(inv.client || '');
  sheet.getRange(r, 6).setValue(Number(inv.suma) || 0);
  sheet.getRange(r, 7).setValue(Number(inv.tva) || 0);
  sheet.getRange(r, 8).setValue(Number(inv.total) || 0);
  sheet.getRange(r, 9).setValue(Number(inv.incasat) || 0);
  sheet.getRange(r, 10).setValue(Number(inv.restant) || 0);
  sheet.getRange(r, 11).setValue(inv.status || 'Emisă');
  sheet.getRange(r, 12).setValue(inv.note || '');
  return {success: true, row: fRow};
}

/**
 * Adaugă contract de mentenanță
 */
function addMentenanta(data) {
  var sheet = getSheet(SH.MENTENANTA);
  var rowNum = sheet.getLastRow() + 1;
  var c = data.contract || data;
  var ultima = c.ultima || new Date();
  var urmatoarea = c.urmatoarea || calcUrmatoareaVerificare(new Date(ultima), c.frecventa || 'Trimestrial');
  
  sheet.appendRow([
    c.client || '',
    c.pid || c.proiectId || '',
    c.tip || c.tipContract || 'Standard',
    c.frecventa || 'Trimestrial',
    ultima,
    urmatoarea,
    c.status || 'Activ',
    Number(c.valoare) || 0,
    c.telefon || '',
    c.echipa || '',
    c.note || '',
    JSON.stringify([typeof ultima === 'string' ? ultima : new Date().toISOString().split('T')[0]])
  ]);
  return {success: true, row: rowNum - 1};
}

/**
 * Actualizează statusul unui contract de mentenanță
 */
function updateMentenantaStatus(mRow, newStatus) {
  var sheet = getSheet(SH.MENTENANTA);
  if (mRow < 1) return {error: 'Invalid row'};
  sheet.getRange(mRow + 1, 7).setValue(newStatus);
  return {success: true, row: mRow, status: newStatus};
}

/**
 * Actualizează complet un contract de mentenanță
 */
function updateMentenantaFull(mRow, contract) {
  var sheet = getSheet(SH.MENTENANTA);
  if (mRow < 1) return {error: 'Invalid row'};
  var r = mRow + 1;
  var c = contract;
  sheet.getRange(r, 1).setValue(c.client || '');
  sheet.getRange(r, 2).setValue(c.pid || '');
  sheet.getRange(r, 3).setValue(c.tip || 'Standard');
  sheet.getRange(r, 4).setValue(c.frecventa || 'Trimestrial');
  sheet.getRange(r, 5).setValue(c.ultima || '');
  sheet.getRange(r, 6).setValue(c.urmatoarea || '');
  sheet.getRange(r, 7).setValue(c.status || 'Activ');
  sheet.getRange(r, 8).setValue(Number(c.valoare) || 0);
  sheet.getRange(r, 9).setValue(c.telefon || '');
  sheet.getRange(r, 10).setValue(c.echipa || '');
  sheet.getRange(r, 11).setValue(c.note || '');
  return {success: true, row: mRow};
}

/**
 * Marchează verificarea efectuată și calculează următoarea
 */
function markMentenantaDone(mRow, ultima, urmatoarea, historyJson) {
  var sheet = getSheet(SH.MENTENANTA);
  if (mRow < 1) return {error: 'Invalid row'};
  var r = mRow + 1;
  sheet.getRange(r, 5).setValue(ultima);
  sheet.getRange(r, 6).setValue(urmatoarea);
  sheet.getRange(r, 12).setValue(historyJson || '[]');
  return {success: true, row: mRow};
}

/**
 * Citește toate programările din PLANIFICARE
 */
function getPlanificare() {
  var sheet = getSheet(SH.PLANIFICARE);
  var data = sheet.getDataRange().getValues();
  if (data.length <= 1) return [];
  
  var result = [];
  for (var i = 1; i < data.length; i++) {
    var row = data[i];
    if (!row[2]) continue; // Skip rows fără Descriere
    result.push({
      row: i,
      data: row[0] ? Utilities.formatDate(new Date(row[0]), 'Europe/Bucharest', 'yyyy-MM-dd') : '',
      ora: row[1] || '08:00',
      descriere: row[2],
      proiectId: row[3] || '',
      echipa: row[4] || '',
      tip: row[5] || 'exec',
      durata: Number(row[6]) || 8,
      prioritate: row[7] || 'normal',
      status: row[8] || 'Planificat',
      note: row[9] || ''
    });
  }
  return result;
}

/**
 * Adaugă programare nouă în PLANIFICARE
 */
function addPlanificareTask(task) {
  var sheet = getSheet(SH.PLANIFICARE);
  var rowNum = sheet.getLastRow() + 1;
  
  sheet.appendRow([
    task.data || new Date(),
    task.ora || '08:00',
    task.desc || '',
    task.pid || '',
    task.echipa || '',
    task.tip || 'exec',
    Number(task.durata) || 8,
    task.prioritate || 'normal',
    'Planificat',
    task.note || ''
  ]);
  
  return {success: true, row: rowNum - 1};
}

/**
 * Actualizează statusul unei programări (Planificat / Finalizat)
 */
function updatePlanificareStatus(planRow, newStatus) {
  var sheet = getSheet(SH.PLANIFICARE);
  if (planRow < 1) return {error: 'Invalid row'};
  sheet.getRange(planRow + 1, 9).setValue(newStatus); // Coloana I = Status
  return {success: true, row: planRow, status: newStatus};
}

/**
 * Actualizează complet o programare existentă
 */
function updatePlanificareTask(planRow, task) {
  var sheet = getSheet(SH.PLANIFICARE);
  if (planRow < 1) return {error: 'Invalid row'};
  var r = planRow + 1;
  sheet.getRange(r, 1).setValue(task.data || '');
  sheet.getRange(r, 2).setValue(task.ora || '08:00');
  sheet.getRange(r, 3).setValue(task.desc || '');
  sheet.getRange(r, 4).setValue(task.pid || '');
  sheet.getRange(r, 5).setValue(task.echipa || '');
  sheet.getRange(r, 6).setValue(task.tip || 'exec');
  sheet.getRange(r, 7).setValue(Number(task.durata) || 8);
  sheet.getRange(r, 8).setValue(task.prioritate || 'normal');
  sheet.getRange(r, 10).setValue(task.note || '');
  return {success: true, row: planRow};
}

/**
 * Citește toate dosarele din PROIECTARE
 */
function getProiectareData() {
  var sheet = getSheet(SH.PROIECTARE);
  var data = sheet.getDataRange().getValues();
  if (data.length <= 1) return [];
  
  var result = [];
  for (var i = 1; i < data.length; i++) {
    var row = data[i];
    if (!row[0]) continue;
    result.push({
      row: i,
      proiectId: row[0],
      tipAviz: row[1] || '',
      status: row[2] || 'Activ',
      deadline: row[3] ? Utilities.formatDate(new Date(row[3]), 'Europe/Bucharest', 'yyyy-MM-dd') : '',
      responsabil: row[4] || '',
      linkDosar: row[5] || '',
      note: row[6] || '',
      checks: row[7] || '[]'
    });
  }
  return result;
}

/**
 * Adaugă dosar proiectare nou
 */
function addProiectareDosar(dosar) {
  var sheet = getSheet(SH.PROIECTARE);
  var rowNum = sheet.getLastRow() + 1;
  sheet.appendRow([
    dosar.pid || '',
    dosar.tip || '',
    dosar.status || 'Activ',
    dosar.deadline || '',
    dosar.resp || '',
    dosar.link || '',
    dosar.note || '',
    '[]'
  ]);
  return {success: true, row: rowNum - 1};
}

/**
 * Actualizează checks + status pe un dosar
 */
function updateProiectareChecks(projRow, newStatus, checksJson) {
  var sheet = getSheet(SH.PROIECTARE);
  if (projRow < 1) return {error: 'Invalid row'};
  var r = projRow + 1;
  sheet.getRange(r, 3).setValue(newStatus); // Status col C
  sheet.getRange(r, 8).setValue(checksJson || '[]'); // Checks col H
  return {success: true, row: projRow};
}

/**
 * Actualizează doar statusul unui dosar
 */
function updateProiectareStatusOnly(projRow, newStatus) {
  var sheet = getSheet(SH.PROIECTARE);
  if (projRow < 1) return {error: 'Invalid row'};
  sheet.getRange(projRow + 1, 3).setValue(newStatus);
  return {success: true, row: projRow, status: newStatus};
}

/**
 * Actualizează complet un dosar de proiectare
 */
function updateProiectareFullDosar(projRow, dosar) {
  var sheet = getSheet(SH.PROIECTARE);
  if (projRow < 1) return {error: 'Invalid row'};
  var r = projRow + 1;
  sheet.getRange(r, 1).setValue(dosar.pid || '');
  sheet.getRange(r, 2).setValue(dosar.tip || '');
  sheet.getRange(r, 3).setValue(dosar.status || 'Activ');
  sheet.getRange(r, 4).setValue(dosar.deadline || '');
  sheet.getRange(r, 5).setValue(dosar.resp || '');
  sheet.getRange(r, 6).setValue(dosar.link || '');
  sheet.getRange(r, 7).setValue(dosar.note || '');
  return {success: true, row: projRow};
}

/**
 * Citește toate lead-urile din CRM
 */
function getCRM() {
  var sheet = getSheet(SH.CRM);
  var data = sheet.getDataRange().getValues();
  if (data.length <= 1) return [];
  
  var result = [];
  for (var i = 1; i < data.length; i++) {
    var row = data[i];
    if (!row[1]) continue; // Skip rows fără Nume
    result.push({
      row: i,
      timestamp: row[0] ? Utilities.formatDate(new Date(row[0]), 'Europe/Bucharest', 'yyyy-MM-dd') : '',
      nume: row[1],
      telefon: row[2],
      email: row[3],
      serviciu: row[4],
      sursa: row[5],
      status: row[6] || 'Nou',
      proiectId: row[7] || '',
      valoare: Number(row[8]) || 0,
      locatie: row[9] || '',
      note: row[10] || ''
    });
  }
  return result;
}

/**
 * Adaugă lead nou în CRM
 */
function addCRM(lead) {
  var sheet = getSheet(SH.CRM);
  var rowNum = sheet.getLastRow() + 1;
  
  sheet.appendRow([
    new Date(),          // Timestamp
    lead.nume || '',     // Nume
    lead.telefon || '',  // Telefon
    lead.email || '',    // Email
    lead.serviciu || '', // Serviciu
    lead.sursa || '',    // Sursa
    'Nou',               // Status
    '',                  // ProiectID (gol inițial)
    Number(lead.valoare) || 0,  // Valoare
    lead.locatie || '',  // Locatie
    lead.note || ''      // Note
  ]);
  
  // Notificare admin
  if (EMAIL_ADMIN) {
    sendEmail(
      EMAIL_ADMIN,
      '🆕 Lead Nou CRM: ' + lead.nume,
      'Lead nou adăugat în CRM:\n\n' +
      'Client: ' + lead.nume + '\n' +
      'Telefon: ' + lead.telefon + '\n' +
      'Serviciu: ' + lead.serviciu + '\n' +
      'Sursă: ' + lead.sursa + '\n' +
      'Valoare: ' + lead.valoare + ' RON\n' +
      'Note: ' + lead.note
    );
  }
  
  return {success: true, row: rowNum - 1};
}

/**
 * Actualizează statusul unui lead în CRM
 */
function updateCRMStatus(crmRow, newStatus) {
  var sheet = getSheet(SH.CRM);
  var data = sheet.getDataRange().getValues();
  
  if (crmRow < 1 || crmRow >= data.length) {
    return {error: 'Invalid CRM row: ' + crmRow};
  }
  
  sheet.getRange(crmRow + 1, 7).setValue(newStatus); // Coloana G = Status (col 7)
  return {success: true, row: crmRow, status: newStatus};
}

/**
 * Convertește un lead din CRM în proiect
 */
function convertCrmToProject(crmRow) {
  var crmSheet = getSheet(SH.CRM);
  var data = crmSheet.getDataRange().getValues();
  
  if (crmRow < 1 || crmRow >= data.length) {
    return {error: 'Invalid CRM row'};
  }
  
  var row = data[crmRow];
  // CRM columns: 0=Timestamp, 1=Nume, 2=Telefon, 3=Email, 4=Serviciu, 5=Sursa, 6=Status, 7=ProiectID, 8=Valoare, 9=Locatie, 10=Note
  var result = addProject({
    client: row[1],
    telefon: row[2],
    email: row[3],
    serviciu: row[4],
    locatie: row[9] || 'Brașov',
    valoare: Number(row[8]) || 0,
    status: 'Contract',
    note: 'Convertit din CRM — ' + (row[10] || '')
  });
  
  // Update CRM row cu Proiect ID
  if (result.success) {
    crmSheet.getRange(crmRow + 1, 8).setValue(result.id); // Coloana H = ProiectID
    crmSheet.getRange(crmRow + 1, 7).setValue('Câștigat');  // Coloana G = Status
  }
  
  return result;
}


// ═══════════════════════════════════════════════════
// AUTO-GENERATE PROJECT ID
// ═══════════════════════════════════════════════════

function generateProjectId() {
  var sheet = getSheet(SH.PROIECTE);
  var data = sheet.getDataRange().getValues();
  var year = new Date().getFullYear();
  var prefix = 'CSSI-' + year + '-';
  
  var maxNum = 0;
  for (var i = 1; i < data.length; i++) {
    var id = String(data[i][0]);
    if (id.startsWith(prefix)) {
      var num = parseInt(id.substring(prefix.length));
      if (num > maxNum) maxNum = num;
    }
  }
  
  return prefix + String(maxNum + 1).padStart(3, '0');
}


// ═══════════════════════════════════════════════════
// AUTOMATED TRIGGERS
// ═══════════════════════════════════════════════════

/**
 * Raport zilnic la 08:00 — trimite email cu rezumatul activității
 * Setup: Triggers > Add trigger > zilnicRaport > Time-driven > Day timer > 8am-9am
 */
function zilnicRaport() {
  if (!EMAIL_ADMIN) return;
  
  var kpi = getKPI();
  var alerts = getAlerts();
  
  var body = '📊 RAPORT ZILNIC — Portal CSSI 3.0\n';
  body += '═══════════════════════════════════\n\n';
  body += '📋 Proiecte active: ' + kpi.proiecteActive + ' / ' + kpi.proiecteTotal + '\n';
  body += '💰 Valoare pipeline: ' + kpi.valoarePipeline.toLocaleString() + ' RON\n';
  body += '📞 Lead-uri noi: ' + kpi.leaduri + '\n';
  body += '🔧 În execuție: ' + kpi.executie + '\n';
  body += '📄 Facturi restante: ' + kpi.facturiRestante + ' (' + kpi.valoareRestante.toLocaleString() + ' RON)\n';
  body += '🔄 Mentenanțe scadente: ' + kpi.mentenanteScadente + '\n\n';
  
  if (alerts.length > 0) {
    body += '🔔 ALERTE:\n';
    body += '─────────\n';
    alerts.forEach(function(a) {
      body += '• ' + a.text + ' [' + a.time + ']\n';
    });
  }
  
  body += '\n\n— Portal CSSI 3.0 · Raport automat';
  
  sendEmail(EMAIL_ADMIN, '📊 Raport Zilnic CSSI — ' + Utilities.formatDate(new Date(), 'Europe/Bucharest', 'dd.MM.yyyy'), body);
}

/**
 * Verificare mentenanță zilnică — alertează la contracte scadente
 * Setup: Triggers > Add trigger > verificaMentenanta > Time-driven > Day timer > 9am-10am
 */
function verificaMentenanta() {
  if (!EMAIL_ADMIN) return;
  
  var mentenanta = getMentenanta();
  var now = new Date();
  var in14 = new Date(now.getTime() + 14 * 24 * 60 * 60 * 1000);
  
  var scadente = mentenanta.filter(function(m) {
    if (!m.urmatoarea || m.status !== 'Activ') return false;
    var d = new Date(m.urmatoarea);
    return d >= now && d <= in14;
  });
  
  if (scadente.length === 0) return;
  
  var body = '🔄 ALERTE MENTENANȚĂ — ' + scadente.length + ' verificări scadente\n\n';
  
  scadente.forEach(function(m) {
    var zile = Math.ceil((new Date(m.urmatoarea) - now) / (24 * 60 * 60 * 1000));
    body += '• ' + m.client + ' (' + m.proiectId + ')\n';
    body += '  Tip: ' + m.tipContract + ' | Frecvență: ' + m.frecventa + '\n';
    body += '  Scadent: ' + m.urmatoarea + ' (' + zile + ' zile)\n\n';
  });
  
  body += 'Programează verificările în Portal CSSI.';
  
  sendEmail(EMAIL_ADMIN, '🔄 Mentenanță: ' + scadente.length + ' verificări scadente', body);
}


// ═══════════════════════════════════════════════════
// HELPER FUNCTIONS
// ═══════════════════════════════════════════════════

function getSheet(name) {
  var ss = SPREADSHEET_ID 
    ? SpreadsheetApp.openById(SPREADSHEET_ID) 
    : SpreadsheetApp.getActiveSpreadsheet();
  var sheet = ss.getSheetByName(name);
  if (!sheet) {
    throw new Error('Sheet "' + name + '" nu există! Creează-l manual.');
  }
  return sheet;
}

function sendEmail(to, subject, body) {
  try {
    MailApp.sendEmail(to, subject, body);
  } catch (e) {
    Logger.log('Email error: ' + e);
  }
}

function calcUrmatoareaVerificare(fromDate, frecventa) {
  var d = new Date(fromDate);
  switch (frecventa) {
    case 'Lunar': d.setMonth(d.getMonth() + 1); break;
    case 'Trimestrial': d.setMonth(d.getMonth() + 3); break;
    case 'Semestrial': d.setMonth(d.getMonth() + 6); break;
    case 'Anual': d.setFullYear(d.getFullYear() + 1); break;
    default: d.setMonth(d.getMonth() + 3);
  }
  return d;
}

function autoCreateMentenanta(projectRow) {
  try {
    addMentenanta({
      client: projectRow[1],
      proiectId: projectRow[0],
      tipContract: 'Standard',
      frecventa: 'Trimestrial',
      note: 'Auto-creat la finalizare proiect'
    });
  } catch (e) {
    Logger.log('Auto mentenanță error: ' + e);
  }
}


// ═══════════════════════════════════════════════════
// SETUP HELPER — Rulează o singură dată pentru a crea sheet-urile
// ═══════════════════════════════════════════════════

function setupSheets() {
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  
  var sheets = {
    'PROIECTE': ['ID', 'Client', 'Telefon', 'Email', 'Serviciu', 'Locatie', 'Valoare', 'Status', 'Echipa', 'DataStart', 'DataFin', 'Note', 'CreatedAt'],
    'CRM': ['Timestamp', 'Nume', 'Telefon', 'Email', 'Serviciu', 'Sursa', 'Status', 'ProiectID', 'Valoare', 'Locatie', 'Note'],
    'EXECUTIE': ['Data', 'ProiectID', 'Echipa', 'Ore', 'Muncitori', 'Stadiu', 'Observatii', 'Materiale'],
    'MENTENANTA': ['Client', 'ProiectID', 'TipContract', 'Frecventa', 'UltimaVerificare', 'UrmatoareaVerificare', 'Status', 'Valoare', 'Telefon', 'Echipa', 'Note', 'History'],
    'PROIECTARE': ['ProiectID', 'TipAviz', 'Status', 'Deadline', 'Responsabil', 'LinkDosar', 'Note', 'Checks'],
    'MATERIALE': ['Data', 'ProiectID', 'Material', 'Cantitate', 'Urgenta', 'Cost', 'Status', 'Solicitant', 'Furnizor', 'Note'],
    'INVENTAR': ['Nume', 'Cod', 'Categorie', 'Stoc', 'Unitate', 'Minim', 'Pret', 'Furnizor', 'Locatie', 'Note', 'History'],
    'FINANCIAR': ['ProiectID', 'NrFactura', 'DataFactura', 'Scadenta', 'Client', 'Suma', 'TVA', 'Total', 'Incasat', 'Restant', 'Status', 'Note', 'Payments'],
    'SOCIAL': ['Data', 'Platforma', 'Continut', 'Status', 'Tip', 'Note'],
    'PLANIFICARE': ['Data', 'Ora', 'Descriere', 'ProiectID', 'Echipa', 'Tip', 'Durata', 'Prioritate', 'Status', 'Note'],
    'CONFIG': ['Key', 'Value']
  };
  
  Object.keys(sheets).forEach(function(name) {
    var sheet = ss.getSheetByName(name);
    if (!sheet) {
      sheet = ss.insertSheet(name);
    }
    
    var headers = sheets[name];
    var headerRange = sheet.getRange(1, 1, 1, headers.length);
    headerRange.setValues([headers]);
    headerRange.setFontWeight('bold');
    headerRange.setBackground('#0f172a');
    headerRange.setFontColor('#ffffff');
    sheet.setFrozenRows(1);
    
    // Auto-resize columns
    for (var i = 1; i <= headers.length; i++) {
      sheet.autoResizeColumn(i);
    }
  });
  
  // Add config defaults
  var configSheet = ss.getSheetByName('CONFIG');
  configSheet.appendRow(['email_admin', EMAIL_ADMIN]);
  configSheet.appendRow(['versiune', '3.0']);
  configSheet.appendRow(['data_setup', new Date()]);
  
  Logger.log('✅ Setup complet! ' + Object.keys(sheets).length + ' sheet-uri create.');
}


// ═══════════════════════════════════════════════════
// DATA VALIDATION SETUP — Rulează după setupSheets()
// ═══════════════════════════════════════════════════

function setupValidation() {
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  
  // PROIECTE — Status dropdown
  var proiecte = ss.getSheetByName('PROIECTE');
  var statusRule = SpreadsheetApp.newDataValidation()
    .requireValueInList(STATUSES, true)
    .setAllowInvalid(false)
    .build();
  proiecte.getRange('H2:H1000').setDataValidation(statusRule);
  
  // PROIECTE — Serviciu dropdown
  var servicii = ['Detecție Incendiu', 'Alarmă Antiefracție', 'Supraveghere Video', 'Instalații Electrice', 'Instalații Sanitare', 'Automatizări Porți', 'Proiect Complex'];
  var serviciuRule = SpreadsheetApp.newDataValidation()
    .requireValueInList(servicii, true)
    .build();
  proiecte.getRange('E2:E1000').setDataValidation(serviciuRule);
  
  // MENTENANTA — Status dropdown
  var mentenanta = ss.getSheetByName('MENTENANTA');

  // CRM — Status dropdown
  var crm = ss.getSheetByName('CRM');
  var crmStatusRule = SpreadsheetApp.newDataValidation()
    .requireValueInList(['Nou', 'Contactat', 'Ofertă trimisă', 'Negociere', 'Câștigat', 'Pierdut'], true)
    .setAllowInvalid(false)
    .build();
  crm.getRange('G2:G1000').setDataValidation(crmStatusRule);

  // CRM — Serviciu dropdown
  crm.getRange('E2:E1000').setDataValidation(serviciuRule);

  // PLANIFICARE — Tip dropdown
  var planificare = ss.getSheetByName('PLANIFICARE');
  var planTipRule = SpreadsheetApp.newDataValidation()
    .requireValueInList(['exec', 'design', 'maint', 'survey'], true)
    .build();
  planificare.getRange('F2:F1000').setDataValidation(planTipRule);

  // PLANIFICARE — Status dropdown
  var planStatusRule = SpreadsheetApp.newDataValidation()
    .requireValueInList(['Planificat', 'Finalizat', 'Anulat'], true)
    .setAllowInvalid(false)
    .build();
  planificare.getRange('I2:I1000').setDataValidation(planStatusRule);

  // PLANIFICARE — Prioritate dropdown
  var planPrioRule = SpreadsheetApp.newDataValidation()
    .requireValueInList(['low', 'normal', 'urgent'], true)
    .build();
  planificare.getRange('H2:H1000').setDataValidation(planPrioRule);

  // PROIECTARE — Status dropdown
  var proiectare = ss.getSheetByName('PROIECTARE');
  var projStatusRule = SpreadsheetApp.newDataValidation()
    .requireValueInList(['Activ', 'În așteptare', 'Blocat', 'Finalizat'], true)
    .setAllowInvalid(false)
    .build();
  proiectare.getRange('C2:C1000').setDataValidation(projStatusRule);

  // PROIECTARE — Tip Aviz dropdown
  var projTipRule = SpreadsheetApp.newDataValidation()
    .requireValueInList(['Aviz ISU', 'Proiect Electric', 'Proiect Detecție Incendiu', 'Proiect CCTV', 'Proiect Control Acces', 'Documentație Recepție'], true)
    .build();
  proiectare.getRange('B2:B1000').setDataValidation(projTipRule);

  // MENTENANTA
  var mStatusRule = SpreadsheetApp.newDataValidation()
    .requireValueInList(['Activ', 'Expirat', 'Suspendat'], true)
    .build();
  mentenanta.getRange('G2:G1000').setDataValidation(mStatusRule);
  
  // MENTENANTA — Frecventa dropdown
  var frecventaRule = SpreadsheetApp.newDataValidation()
    .requireValueInList(['Lunar', 'Trimestrial', 'Semestrial', 'Anual'], true)
    .build();
  mentenanta.getRange('D2:D1000').setDataValidation(frecventaRule);
  
  // FINANCIAR — Status dropdown
  var financiar = ss.getSheetByName('FINANCIAR');
  var fStatusRule = SpreadsheetApp.newDataValidation()
    .requireValueInList(['Emisă', 'Parțial Încasată', 'Încasată', 'Restantă', 'Anulată'], true)
    .build();
  financiar.getRange('K2:K1000').setDataValidation(fStatusRule);
  
  // MATERIALE — Urgenta dropdown
  var materiale = ss.getSheetByName('MATERIALE');
  var urgRule = SpreadsheetApp.newDataValidation()
    .requireValueInList(['Scăzută', 'Normal', 'Urgentă', 'Critică'], true)
    .build();
  materiale.getRange('E2:E1000').setDataValidation(urgRule);
  
  // MATERIALE — Status dropdown
  var matStatusRule = SpreadsheetApp.newDataValidation()
    .requireValueInList(['Solicitat', 'Aprobat', 'Comandat', 'Livrat', 'Anulat'], true)
    .build();
  materiale.getRange('G2:G1000').setDataValidation(matStatusRule);

  // INVENTAR — Categorie dropdown
  var inventar = ss.getSheetByName('INVENTAR');
  if (inventar) {
    var invCatRule = SpreadsheetApp.newDataValidation()
      .requireValueInList(['Detectoare', 'Camere IP', 'Cabluri', 'Centrale', 'Sirene', 'Accesorii', 'Echipamente rețea', 'Automatizări', 'Altele'], true)
      .build();
    inventar.getRange('C2:C1000').setDataValidation(invCatRule);

    var invUnitRule = SpreadsheetApp.newDataValidation()
      .requireValueInList(['buc', 'm', 'cutie', 'rolă', 'set'], true)
      .build();
    inventar.getRange('E2:E1000').setDataValidation(invUnitRule);
  }

  // SOCIAL — Status dropdown
  var social = ss.getSheetByName('SOCIAL');
  if (social) {
    var socialStatusRule = SpreadsheetApp.newDataValidation()
      .requireValueInList(['Idee', 'Draft', 'Programat', 'Publicat'], true)
      .build();
    social.getRange('D2:D1000').setDataValidation(socialStatusRule);

    var socialTipRule = SpreadsheetApp.newDataValidation()
      .requireValueInList(['Foto', 'Video', 'Carusel', 'Story', 'Reel', 'Text'], true)
      .build();
    social.getRange('E2:E1000').setDataValidation(socialTipRule);
  }
  
  Logger.log('✅ Validări setate pe toate sheet-urile!');
}
