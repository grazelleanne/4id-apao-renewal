(function () {
  function xml(value) {
    return String(value == null ? '' : value)
      .replace(/[\u0000-\u0008\u000B\u000C\u000E-\u001F]/g, '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&apos;');
  }

  function cell(value, style, mergeAcross, type) {
    var merge = mergeAcross ? ' ss:MergeAcross="' + mergeAcross + '"' : '';
    return '<Cell ss:StyleID="' + (style || 'Body') + '"' + merge + '><Data ss:Type="' + (type || 'String') + '">' + xml(value) + '</Data></Cell>';
  }

  window.exportRpcspExcel = function (report) {
    var rows = report.rows || [];
    var tableRows = rows.map(function (row) {
      return '<Row ss:AutoFitHeight="1">' +
        cell(row.article, 'BodyLeft') +
        cell(row.description, 'BodyLeft') +
        cell(row.propertyNumber, 'Body') +
        cell(row.unit, 'Body') +
        cell(row.unitValue, 'Money', 0, 'Number') +
        cell(row.balance, 'Body', 0, 'Number') +
        cell(row.onHand, 'Body', 0, 'Number') +
        cell(row.shortageQuantity || '', 'Body') +
        cell(row.shortageValue || '', row.shortageValue ? 'Money' : 'Body', 0, row.shortageValue ? 'Number' : 'String') +
        cell(row.remarks, 'BodyLeft') +
        cell(row.date, 'Body') +
      '</Row>';
    }).join('');

    var workbook = '<?xml version="1.0"?>' +
      '<?mso-application progid="Excel.Sheet"?>' +
      '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:x="urn:schemas-microsoft-com:office:excel">' +
      '<Styles>' +
        '<Style ss:ID="Default" ss:Name="Normal"><Alignment ss:Vertical="Center"/><Font ss:FontName="Arial" ss:Size="9"/></Style>' +
        '<Style ss:ID="Title"><Alignment ss:Horizontal="Center"/><Font ss:FontName="Arial" ss:Size="14" ss:Bold="1"/></Style>' +
        '<Style ss:ID="Subtitle"><Alignment ss:Horizontal="Center"/><Font ss:FontName="Arial" ss:Size="10"/></Style>' +
        '<Style ss:ID="Info"><Font ss:FontName="Arial" ss:Size="10"/></Style>' +
        '<Style ss:ID="Header"><Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders><Font ss:FontName="Arial" ss:Size="9" ss:Bold="1"/></Style>' +
        '<Style ss:ID="Body"><Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>' +
        '<Style ss:ID="BodyLeft" ss:Parent="Body"><Alignment ss:Horizontal="Left" ss:Vertical="Center" ss:WrapText="1"/></Style>' +
        '<Style ss:ID="Money" ss:Parent="Body"><NumberFormat ss:Format="₱#,##0.00"/></Style>' +
        '<Style ss:ID="Summary"><Alignment ss:Horizontal="Right"/><Font ss:FontName="Arial" ss:Size="10" ss:Bold="1"/><NumberFormat ss:Format="₱#,##0.00"/></Style>' +
        '<Style ss:ID="SignTitle"><Font ss:FontName="Arial" ss:Size="10" ss:Bold="1"/></Style>' +
        '<Style ss:ID="SignName"><Font ss:FontName="Arial" ss:Size="10" ss:Bold="1"/></Style>' +
        '<Style ss:ID="Sign"><Font ss:FontName="Arial" ss:Size="10"/></Style>' +
      '</Styles>' +
      '<Worksheet ss:Name="RPCSP"><Table ss:ExpandedColumnCount="11" ss:ExpandedRowCount="' + (rows.length + 19) + '" x:FullColumns="1" x:FullRows="1">' +
        '<Column ss:Width="58"/><Column ss:Width="110"/><Column ss:Width="130"/><Column ss:Width="66"/><Column ss:Width="78"/><Column ss:Width="72"/><Column ss:Width="66"/><Column ss:Width="60"/><Column ss:Width="70"/><Column ss:Width="90"/><Column ss:Width="78"/>' +
        '<Row ss:Height="22">' + cell('REPORT ON THE PHYSICAL COUNT OF SEMI-EXPENDABLE PROPERTY', 'Title', 10) + '</Row>' +
        '<Row>' + cell('MILITARY, POLICE & SECURITY EQUIPMENT (ACCOUNT CODE: 1-04-05-090-00)', 'Subtitle', 10) + '</Row>' +
        '<Row>' + cell('(Type of Property, Plant and Equipment)', 'Subtitle', 10) + '</Row>' +
        '<Row>' + cell('As of ' + report.asOf, 'Subtitle', 10) + '</Row>' +
        '<Row ss:Height="10">' + cell('', 'Info', 10) + '</Row>' +
        '<Row>' + cell('Fund Cluster: ' + report.fund, 'Info', 10) + '</Row>' +
        '<Row>' + cell('For which: ' + report.officer + ', ' + report.designation + ' is accountable, having assumed such accountability on ' + report.assumption + '.', 'Info', 10) + '</Row>' +
        '<Row ss:Height="34">' +
          cell('ARTICLE', 'Header') + cell('DESCRIPTION', 'Header') + cell('SEMI-EXPENDABLE PROPERTY NUMBER', 'Header') + cell('UNIT OF MEASURE', 'Header') + cell('UNIT VALUE', 'Header') + cell('BALANCE PER PROPERTY CARD', 'Header') + cell('ON HAND PER COUNT', 'Header') + cell('SHORTAGE / OVERAGE QUANTITY', 'Header') + cell('SHORTAGE / OVERAGE VALUE', 'Header') + cell('REMARKS', 'Header') + cell('DATE', 'Header') +
        '</Row>' + tableRows +
        '<Row><Cell ss:Index="7" ss:StyleID="Summary" ss:MergeAcross="1"><Data ss:Type="String">Total Property Count: ' + xml(rows.length) + '</Data></Cell>' + cell(report.totalValue, 'Summary', 2, 'Number') + '</Row>' +
        '<Row ss:Height="12">' + cell('', 'Info', 10) + '</Row>' +
        '<Row>' + cell('Certified Correct by:', 'SignTitle', 2) + cell('Approved by:', 'SignTitle', 2) + cell('Witnessed by:', 'SignTitle', 4) + '</Row>' +
        '<Row ss:Height="34">' + cell('', 'Sign', 10) + '</Row>' +
        '<Row>' + cell('MS ROSEMARIE O VILBAR, MPA', 'SignName', 2) + cell('ANTHONY A BACUS', 'SignName', 2) + cell('Mr Darrell Antoni J Wong', 'SignName', 4) + '</Row>' +
        '<Row>' + cell('CHIEF, PAOGS, APAO, PA', 'Sign', 2) + cell('LTC INF (GSC) PA', 'Sign', 2) + cell('Rep, COA, HPA', 'Sign', 4) + '</Row>' +
        '<Row>' + cell('TEAM LEADER', 'Sign', 2) + cell('Commanding Officer', 'Sign', 2) + cell('Member', 'Sign', 4) + '</Row>' +
        '<Row ss:Height="24">' + cell('', 'Sign', 10) + '</Row>' +
        '<Row>' + cell('Mr Jan Harold C Novo CE', 'SignName', 2) + cell('', 'Sign', 7) + '</Row>' +
        '<Row>' + cell('UPO, 4ID, PA', 'Sign', 2) + cell('', 'Sign', 7) + '</Row>' +
        '<Row>' + cell('Member', 'Sign', 2) + cell('', 'Sign', 7) + '</Row>' +
      '</Table><WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel"><PageSetup><Layout x:Orientation="Landscape"/><PageMargins x:Bottom="0.3" x:Left="0.3" x:Right="0.3" x:Top="0.3"/></PageSetup><FitToPage/><Print><FitWidth>1</FitWidth><FitHeight>0</FitHeight><ValidPrinterInfo/></Print><Selected/></WorksheetOptions></Worksheet></Workbook>';

    var blob = new Blob(['\uFEFF' + workbook], { type: 'application/vnd.ms-excel;charset=utf-8' });
    var url = URL.createObjectURL(blob);
    var link = document.createElement('a');
    link.href = url;
    link.download = report.filename || 'RPCSP.xls';
    link.click();
    URL.revokeObjectURL(url);
  };

  window.exportPersonnelRenewalExcel = function (report) {
    var rows = report.rows || [];
    var widths = [42, 78, 82, 82, 82, 82, 92, 78, 118, 100, 58];
    var headers = [
      'ITEM #', 'DATE OF VALIDITY', 'STATUS', 'LAST NAME', 'FIRST NAME',
      'MIDDLE NAME', 'AFP SERIAL #', 'DATE OF BIRTH',
      'NOMENCLATURE OF PISTOL', 'PISTOL SERIAL #', 'QTY AMMO'
    ];

    var tableRows = rows.map(function (row) {
      return '<Row ss:AutoFitHeight="1">' +
        cell(row.itemNumber, 'Body', 0, 'Number') +
        cell(row.dateOfValidity, 'Body') +
        cell(row.status, 'Body') +
        cell(row.lastName, 'BodyLeft') +
        cell(row.firstName, 'BodyLeft') +
        cell(row.middleName, 'BodyLeft') +
        cell(row.afpSerialNumber, 'Body') +
        cell(row.dateOfBirth, 'Body') +
        cell(row.pistolNomenclature, 'BodyLeft') +
        cell(row.pistolSerialNumber, 'Body') +
        cell(row.qtyAmmo, 'Body', 0, 'Number') +
      '</Row>';
    }).join('');

    var workbook = '<?xml version="1.0"?>' +
      '<?mso-application progid="Excel.Sheet"?>' +
      '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:x="urn:schemas-microsoft-com:office:excel">' +
      '<Styles>' +
        '<Style ss:ID="Default" ss:Name="Normal"><Alignment ss:Vertical="Center"/><Font ss:FontName="Arial" ss:Size="9"/></Style>' +
        '<Style ss:ID="Title"><Alignment ss:Horizontal="Center"/><Font ss:FontName="Arial" ss:Size="14" ss:Bold="1"/></Style>' +
        '<Style ss:ID="Subtitle"><Alignment ss:Horizontal="Center"/><Font ss:FontName="Arial" ss:Size="10"/></Style>' +
        '<Style ss:ID="Info"><Font ss:FontName="Arial" ss:Size="10"/></Style>' +
        '<Style ss:ID="Header"><Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders><Font ss:FontName="Arial" ss:Size="9" ss:Bold="1"/></Style>' +
        '<Style ss:ID="Body"><Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>' +
        '<Style ss:ID="BodyLeft" ss:Parent="Body"><Alignment ss:Horizontal="Left" ss:Vertical="Center" ss:WrapText="1"/></Style>' +
        '<Style ss:ID="Summary"><Alignment ss:Horizontal="Right"/><Font ss:FontName="Arial" ss:Size="10" ss:Bold="1"/></Style>' +
        '<Style ss:ID="SignTitle"><Font ss:FontName="Arial" ss:Size="10" ss:Bold="1"/></Style>' +
        '<Style ss:ID="SignName"><Alignment ss:Horizontal="Center"/><Font ss:FontName="Arial" ss:Size="10" ss:Bold="1"/></Style>' +
        '<Style ss:ID="Sign"><Alignment ss:Horizontal="Center"/><Font ss:FontName="Arial" ss:Size="10"/></Style>' +
      '</Styles>' +
      '<Worksheet ss:Name="Personnel Renewal"><Table ss:ExpandedColumnCount="11" ss:ExpandedRowCount="' + (rows.length + 5) + '" x:FullColumns="1" x:FullRows="1">' +
        widths.map(function (width) { return '<Column ss:Width="' + width + '"/>'; }).join('') +
        '<Row ss:Height="22">' + cell('ARMY PROPERTY ACCOUNTABILITY OFFICE', 'Title', 10) + '</Row>' +
        '<Row>' + cell('PERSONNEL RENEWAL REPORT', 'Title', 10) + '</Row>' +
        '<Row>' + cell('Validity period: ' + (report.period || 'All Records') + ' | Generated as of ' + report.generatedDate, 'Subtitle', 10) + '</Row>' +
        '<Row ss:Height="10">' + cell('', 'Info', 10) + '</Row>' +
        '<Row ss:Height="34">' + headers.map(function (header) { return cell(header, 'Header'); }).join('') + '</Row>' +
        tableRows +
      '</Table><WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel"><FreezePanes/><FrozenNoSplit/><SplitHorizontal>5</SplitHorizontal><TopRowBottomPane>5</TopRowBottomPane><ActivePane>2</ActivePane><PageSetup><Layout x:Orientation="Landscape"/><PageMargins x:Bottom="0.3" x:Left="0.3" x:Right="0.3" x:Top="0.3"/></PageSetup><FitToPage/><Print><FitWidth>1</FitWidth><FitHeight>0</FitHeight><ValidPrinterInfo/></Print><Selected/></WorksheetOptions></Worksheet></Workbook>';

    var blob = new Blob(['\uFEFF' + workbook], { type: 'application/vnd.ms-excel;charset=utf-8' });
    var url = URL.createObjectURL(blob);
    var link = document.createElement('a');
    link.href = url;
    link.download = report.filename || 'APAO_Personnel_Renewal_Report.xls';
    link.click();
    URL.revokeObjectURL(url);
  };

  window.exportPersonnelListExcel = function (report) {
    var rows = report.rows || [];
    var headers = [
      'ITEM #', 'DATE OF VALIDITY', 'STATUS', 'RANK', 'LAST NAME', 'FIRST NAME',
      'MIDDLE NAME', 'AFP SERIAL #', 'AFOS / MOS', 'BRANCH', 'UNIT / OFFICE',
      'DATE OF BIRTH', 'NOMENCLATURE OF PISTOL', 'PISTOL SERIAL #', 'QTY AMMO', 'EMAIL'
    ];
    var widths = [42, 76, 72, 48, 82, 82, 82, 92, 62, 76, 112, 76, 120, 94, 54, 155];

    function textCell(value, style) {
      return cell(value, style || 'PersonnelBody');
    }

    function numberCell(value) {
      return value === '' || value == null || !Number.isFinite(Number(value))
        ? textCell('')
        : cell(Number(value), 'PersonnelBody', 0, 'Number');
    }

    function statusLabel(value) {
      return String(value || 'pending')
        .replace(/_/g, ' ')
        .replace(/\b\w/g, function (letter) { return letter.toUpperCase(); });
    }

    var tableRows = rows.map(function (row) {
      return '<Row ss:AutoFitHeight="1">' +
        numberCell(row.itemNumber) +
        textCell(row.dateOfValidity) +
        textCell(statusLabel(row.approvedStatus)) +
        textCell(row.rank) +
        textCell(row.lastName, 'PersonnelBodyLeft') +
        textCell(row.firstName, 'PersonnelBodyLeft') +
        textCell(row.middleName, 'PersonnelBodyLeft') +
        textCell(row.afpSerialNumber) +
        textCell(row.afosMos) +
        textCell(row.branch) +
        textCell(row.unit, 'PersonnelBodyLeft') +
        textCell(row.dateOfBirth) +
        textCell(row.pistolNomenclature, 'PersonnelBodyLeft') +
        textCell(row.pistolSerialNumber) +
        numberCell(row.qtyAmmo) +
        textCell(row.email, 'PersonnelBodyLeft') +
      '</Row>';
    }).join('');

    var workbook = '<?xml version="1.0"?>' +
      '<?mso-application progid="Excel.Sheet"?>' +
      '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:x="urn:schemas-microsoft-com:office:excel">' +
      '<Styles>' +
        '<Style ss:ID="Default" ss:Name="Normal"><Alignment ss:Vertical="Center"/><Font ss:FontName="Arial" ss:Size="9"/></Style>' +
        '<Style ss:ID="PersonnelTitle"><Alignment ss:Horizontal="Center" ss:Vertical="Center"/><Font ss:FontName="Arial" ss:Size="14" ss:Bold="1" ss:Color="#17365D"/></Style>' +
        '<Style ss:ID="PersonnelSubtitle"><Alignment ss:Horizontal="Center"/><Font ss:FontName="Arial" ss:Size="10" ss:Color="#475569"/></Style>' +
        '<Style ss:ID="PersonnelHeader"><Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders><Font ss:FontName="Arial" ss:Size="9" ss:Bold="1" ss:Color="#FFFFFF"/><Interior ss:Color="#1F4E78" ss:Pattern="Solid"/></Style>' +
        '<Style ss:ID="PersonnelBody"><Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D9E2F3"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D9E2F3"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D9E2F3"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D9E2F3"/></Borders></Style>' +
        '<Style ss:ID="PersonnelBodyLeft" ss:Parent="PersonnelBody"><Alignment ss:Horizontal="Left" ss:Vertical="Center" ss:WrapText="1"/></Style>' +
      '</Styles>' +
      '<Worksheet ss:Name="Personnel List"><Table ss:ExpandedColumnCount="16" ss:ExpandedRowCount="' + (rows.length + 5) + '" x:FullColumns="1" x:FullRows="1">' +
        widths.map(function (width) { return '<Column ss:Width="' + width + '"/>'; }).join('') +
        '<Row ss:Height="24">' + cell('ARMY PROPERTY ACCOUNTABILITY OFFICE', 'PersonnelTitle', 15) + '</Row>' +
        '<Row ss:Height="20">' + cell('LIST OF PERSONNEL', 'PersonnelTitle', 15) + '</Row>' +
        '<Row>' + cell('Generated as of ' + report.generatedDate, 'PersonnelSubtitle', 15) + '</Row>' +
        '<Row ss:Height="8">' + cell('', 'PersonnelSubtitle', 15) + '</Row>' +
        '<Row ss:Height="36">' + headers.map(function (header) { return cell(header, 'PersonnelHeader'); }).join('') + '</Row>' +
        tableRows +
      '</Table><WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel"><FreezePanes/><FrozenNoSplit/><SplitHorizontal>5</SplitHorizontal><TopRowBottomPane>5</TopRowBottomPane><ActivePane>2</ActivePane><PageSetup><Layout x:Orientation="Landscape"/><PageMargins x:Bottom="0.3" x:Left="0.25" x:Right="0.25" x:Top="0.3"/></PageSetup><FitToPage/><Print><FitWidth>1</FitWidth><FitHeight>0</FitHeight><ValidPrinterInfo/></Print><Selected/></WorksheetOptions></Worksheet></Workbook>';

    var blob = new Blob(['\uFEFF' + workbook], { type: 'application/vnd.ms-excel;charset=utf-8' });
    var url = URL.createObjectURL(blob);
    var link = document.createElement('a');
    link.href = url;
    link.download = report.filename || 'APAO_Personnel_List.xls';
    link.click();
    window.setTimeout(function () { URL.revokeObjectURL(url); }, 0);
  };

  window.exportAuditLogExcel = function (report) {
    var rows = report.rows || [];
    var headers = ['#', 'DATE & TIME', 'USER', 'ROLE', 'ACTION', 'TARGET', 'IP ADDRESS', 'DETAILS'];
    var widths = [38, 128, 88, 62, 112, 132, 82, 260];

    function readableLabel(value) {
      return String(value || '')
        .replace(/([a-z0-9])([A-Z])/g, '$1 $2')
        .replace(/_/g, ' ')
        .replace(/\b\w/g, function (letter) { return letter.toUpperCase(); });
    }

    function readableValue(value) {
      if (value == null) return '';
      if (Array.isArray(value)) return value.map(readableValue).join(', ');
      if (typeof value === 'object') {
        return Object.keys(value).map(function (key) {
          return readableLabel(key) + ': ' + readableValue(value[key]);
        }).join(', ');
      }
      if (typeof value === 'boolean') return value ? 'Yes' : 'No';
      return String(value);
    }

    function readableDetails(details) {
      if (!details) return '';
      if (typeof details === 'string') {
        try { details = JSON.parse(details); } catch (error) { return details; }
      }
      if (typeof details !== 'object') return String(details);
      return Object.keys(details).map(function (key) {
        return readableLabel(key) + ': ' + readableValue(details[key]);
      }).join('; ');
    }

    var tableRows = rows.map(function (row, index) {
      return '<Row ss:AutoFitHeight="1">' +
        cell(row.number == null ? index + 1 : row.number, 'AuditBody', 0, 'Number') +
        cell(row.createdAt, 'AuditBody') +
        cell(row.userName, 'AuditBodyLeft') +
        cell(readableLabel(row.userRole), 'AuditBody') +
        cell(row.actionLabel || readableLabel(row.action), 'AuditBodyLeft') +
        cell(row.target, 'AuditBodyLeft') +
        cell(row.ipAddress, 'AuditBody') +
        cell(readableDetails(row.details), 'AuditDetails') +
      '</Row>';
    }).join('');

    var workbook = '<?xml version="1.0"?>' +
      '<?mso-application progid="Excel.Sheet"?>' +
      '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:x="urn:schemas-microsoft-com:office:excel">' +
      '<Styles>' +
        '<Style ss:ID="Default" ss:Name="Normal"><Alignment ss:Vertical="Center"/><Font ss:FontName="Arial" ss:Size="9"/></Style>' +
        '<Style ss:ID="AuditTitle"><Alignment ss:Horizontal="Center" ss:Vertical="Center"/><Font ss:FontName="Arial" ss:Size="14" ss:Bold="1" ss:Color="#17365D"/></Style>' +
        '<Style ss:ID="AuditSubtitle"><Alignment ss:Horizontal="Center"/><Font ss:FontName="Arial" ss:Size="10" ss:Color="#475569"/></Style>' +
        '<Style ss:ID="AuditHeader"><Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders><Font ss:FontName="Arial" ss:Size="9" ss:Bold="1" ss:Color="#FFFFFF"/><Interior ss:Color="#1F4E78" ss:Pattern="Solid"/></Style>' +
        '<Style ss:ID="AuditBody"><Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D9E2F3"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D9E2F3"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D9E2F3"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D9E2F3"/></Borders></Style>' +
        '<Style ss:ID="AuditBodyLeft" ss:Parent="AuditBody"><Alignment ss:Horizontal="Left" ss:Vertical="Center" ss:WrapText="1"/></Style>' +
        '<Style ss:ID="AuditDetails" ss:Parent="AuditBody"><Alignment ss:Horizontal="Left" ss:Vertical="Top" ss:WrapText="1"/></Style>' +
      '</Styles>' +
      '<Worksheet ss:Name="Audit Log"><Table ss:ExpandedColumnCount="8" ss:ExpandedRowCount="' + (rows.length + 5) + '" x:FullColumns="1" x:FullRows="1">' +
        widths.map(function (width) { return '<Column ss:Width="' + width + '"/>'; }).join('') +
        '<Row ss:Height="24">' + cell('ARMY PROPERTY ACCOUNTABILITY OFFICE', 'AuditTitle', 7) + '</Row>' +
        '<Row ss:Height="20">' + cell('AUDIT LOG', 'AuditTitle', 7) + '</Row>' +
        '<Row>' + cell('Date range: ' + (report.period || 'All dates') + ' | Generated as of ' + report.generatedDate, 'AuditSubtitle', 7) + '</Row>' +
        '<Row ss:Height="8">' + cell('', 'AuditSubtitle', 7) + '</Row>' +
        '<Row ss:Height="34">' + headers.map(function (header) { return cell(header, 'AuditHeader'); }).join('') + '</Row>' +
        tableRows +
      '</Table><WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel"><FreezePanes/><FrozenNoSplit/><SplitHorizontal>5</SplitHorizontal><TopRowBottomPane>5</TopRowBottomPane><ActivePane>2</ActivePane><PageSetup><Layout x:Orientation="Landscape"/><PageMargins x:Bottom="0.3" x:Left="0.25" x:Right="0.25" x:Top="0.3"/></PageSetup><FitToPage/><Print><FitWidth>1</FitWidth><FitHeight>0</FitHeight><ValidPrinterInfo/></Print><Selected/></WorksheetOptions></Worksheet></Workbook>';

    var blob = new Blob(['\uFEFF' + workbook], { type: 'application/vnd.ms-excel;charset=utf-8' });
    var url = URL.createObjectURL(blob);
    var link = document.createElement('a');
    link.href = url;
    link.download = report.filename || 'APAO_Audit_Log.xls';
    link.click();
    window.setTimeout(function () { URL.revokeObjectURL(url); }, 0);
  };
})();
