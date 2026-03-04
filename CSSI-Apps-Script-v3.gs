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
 * 2. CRM: Timestamp | Nume | Telefon | Email | Serviciu | Sursa | Status | ProiectID | Note
 * 3. EXECUTIE: Data | ProiectID | Echipa | Ore | Stadiu | Observatii
 * 4. MENTENANTA: Client | ProiectID | TipContract | Frecventa | UltimaVerificare | UrmatoareaVerificare | Status | Note
 * 5. PROIECTARE: ProiectID | TipAviz | Status | Deadline | Responsabil | LinkDosar | Note
 * 6. MATERIALE: Data | ProiectID | Material | Cantitate | Urgenta | Cost | Status | Solicitant
 * 7. FINANCIAR: ProiectID | NrFactura | DataFactura | Suma | TVA | Total | Incasat | Restant | Status
 * 8. SOCIAL: Data | Platforma | Continut | Status | Programat
 * 9. CONFIG: Key | Value (pentru setări: email_admin, email_notificari, etc.)
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
  FINANCIAR: 'FINANCIAR',
  SOCIAL: 'SOCIAL',
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
      case 'addExecutie':
        result = addExecutie(data);
        break;
      case 'addFactura':
        result = addFactura(data);
        break;
      case 'addMentenanta':
        result = addMentenanta(data);
        break;
      case 'crmToProject':
        result = convertCrmToProject(data.crmRow);
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
      client: row[0],
      proiectId: row[1],
      tipContract: row[2],
      frecventa: row[3],
      ultima: row[4] ? Utilities.formatDate(new Date(row[4]), 'Europe/Bucharest', 'yyyy-MM-dd') : '',
      urmatoarea: row[5] ? Utilities.formatDate(new Date(row[5]), 'Europe/Bucharest', 'yyyy-MM-dd') : '',
      status: row[6],
      note: row[7]
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
      proiectId: row[0],
      nrFactura: row[1],
      dataFactura: row[2] ? Utilities.formatDate(new Date(row[2]), 'Europe/Bucharest', 'yyyy-MM-dd') : '',
      suma: Number(row[3]) || 0,
      tva: Number(row[4]) || 0,
      total: Number(row[5]) || 0,
      incasat: Number(row[6]) || 0,
      restant: Number(row[7]) || 0,
      status: row[8]
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
  sheet.appendRow([
    new Date(),
    data.proiectId || '',
    data.material || '',
    Number(data.cantitate) || 0,
    data.urgenta || 'Normal',
    Number(data.cost) || 0,
    'Solicitat',
    data.solicitant || ''
  ]);
  return {success: true};
}

/**
 * Adaugă raport de execuție legat de un proiect
 */
function addExecutie(data) {
  var sheet = getSheet(SH.EXECUTIE);
  sheet.appendRow([
    new Date(),
    data.proiectId || '',
    data.echipa || '',
    Number(data.ore) || 0,
    data.stadiu || '',
    data.observatii || ''
  ]);
  return {success: true};
}

/**
 * Adaugă factură legată de un proiect
 */
function addFactura(data) {
  var sheet = getSheet(SH.FINANCIAR);
  var suma = Number(data.suma) || 0;
  var tva = suma * 0.19;
  var total = suma + tva;
  
  sheet.appendRow([
    data.proiectId || '',
    data.nrFactura || '',
    data.dataFactura || new Date(),
    suma,
    tva,
    total,
    0, // încasat
    total, // restant
    'Emisă'
  ]);
  return {success: true};
}

/**
 * Adaugă contract de mentenanță
 */
function addMentenanta(data) {
  var sheet = getSheet(SH.MENTENANTA);
  
  // Calculează următoarea verificare
  var urmatoarea = calcUrmatoareaVerificare(new Date(), data.frecventa || 'Trimestrial');
  
  sheet.appendRow([
    data.client || '',
    data.proiectId || '',
    data.tipContract || 'Standard',
    data.frecventa || 'Trimestrial',
    new Date(), // ultima verificare
    urmatoarea,
    'Activ',
    data.note || ''
  ]);
  return {success: true};
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
  var result = addProject({
    client: row[1],
    telefon: row[2],
    email: row[3],
    serviciu: row[4],
    status: 'Contract',
    note: 'Convertit din CRM'
  });
  
  // Update CRM row cu Proiect ID
  if (result.success) {
    crmSheet.getRange(crmRow + 1, 8).setValue(result.id); // Coloana ProiectID
    crmSheet.getRange(crmRow + 1, 7).setValue('Câștigat'); // Status CRM
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
    'CRM': ['Timestamp', 'Nume', 'Telefon', 'Email', 'Serviciu', 'Sursa', 'Status', 'ProiectID', 'Note'],
    'EXECUTIE': ['Data', 'ProiectID', 'Echipa', 'Ore', 'Stadiu', 'Observatii'],
    'MENTENANTA': ['Client', 'ProiectID', 'TipContract', 'Frecventa', 'UltimaVerificare', 'UrmatoareaVerificare', 'Status', 'Note'],
    'PROIECTARE': ['ProiectID', 'TipAviz', 'Status', 'Deadline', 'Responsabil', 'LinkDosar', 'Note'],
    'MATERIALE': ['Data', 'ProiectID', 'Material', 'Cantitate', 'Urgenta', 'Cost', 'Status', 'Solicitant'],
    'FINANCIAR': ['ProiectID', 'NrFactura', 'DataFactura', 'Suma', 'TVA', 'Total', 'Incasat', 'Restant', 'Status'],
    'SOCIAL': ['Data', 'Platforma', 'Continut', 'Status', 'Programat'],
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
  financiar.getRange('I2:I1000').setDataValidation(fStatusRule);
  
  // MATERIALE — Urgenta dropdown
  var materiale = ss.getSheetByName('MATERIALE');
  var urgRule = SpreadsheetApp.newDataValidation()
    .requireValueInList(['Scăzută', 'Normal', 'Urgentă', 'Critică'], true)
    .build();
  materiale.getRange('E2:E1000').setDataValidation(urgRule);
  
  // MATERIALE — Status dropdown
  var matStatusRule = SpreadsheetApp.newDataValidation()
    .requireValueInList(['Solicitat', 'Comandat', 'Livrat', 'Anulat'], true)
    .build();
  materiale.getRange('G2:G1000').setDataValidation(matStatusRule);
  
  Logger.log('✅ Validări setate pe toate sheet-urile!');
}
