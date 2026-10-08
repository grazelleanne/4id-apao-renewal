<style id="par-receipt-styles">
    *{
        box-sizing:border-box;
    }

    body{
        background:#fff;
        color:#111;
        font-family:Arial,Helvetica,sans-serif;
    }

    .par-receipt{
        position:relative;
        width:190mm;
        min-height:270mm;
        margin:auto;
        padding:10mm 9mm 7mm;
        background:#fff;
        border:1px solid #cfcfcf;
        font-size:10.5px;
    }

    .par-motto{
        width:178mm;
        margin:0 auto 8px;
        text-align:center;
        color:#174f2c;
        font-size:10.5px;
        font-weight:800;
        border-bottom:4px double #b8951a;
        padding:0 0 5px;
    }

    .par-receipt header{
        text-align:center;
        margin-bottom:18px;
    }

    .par-receipt h1{
        font-size:22px;
        line-height:1;
        margin:0 0 3px;
        font-weight:800;
    }

    .par-receipt header p{
        font-size:11px;
        line-height:1.15;
        margin:0;
    }

    .par-number{
        margin:0 0 7px 2px;
        font-size:11px;
    }

    .par-number span{
        font-weight:800;
        color:#174f2c;
        margin-left:8px;
    }

    .par-watermark{
        position:absolute;
        top:104mm;
        left:50%;
        width:72mm;
        transform:translateX(-50%);
        opacity:.09;
        z-index:0;
    }

    .par-receipt table{
        position:relative;
        z-index:1;
        width:100%;
        border-collapse:collapse;
        table-layout:fixed;
    }

    .par-receipt th,
    .par-receipt td{
        border:1px solid #333;
        padding:4px 6px;
        vertical-align:top;
    }

    .par-receipt th{
        background:#dfeeda;
        text-align:center;
        font-size:10px;
        font-weight:800;
    }

    .par-items th:nth-child(1){width:13%;}
    .par-items th:nth-child(2){width:10%;}
    .par-items th:nth-child(3){width:43%;}
    .par-items th:nth-child(4){width:21%;}
    .par-items th:nth-child(5){width:13%;}

    .par-items td{
        text-align:center;
    }

    .par-items .description{
        text-align:left;
        line-height:1.22;
    }

    .par-items .description ul{
        margin:4px 0 0 14px;
        padding:0;
        list-style:disc;
    }

    .par-items .main-item{
        height:52mm;
    }

    .par-items .total td{
        height:7mm;
        font-weight:800;
        text-align:center;
    }

    .par-items .total td:first-child{
        text-align:center;
    }

    .remarks{
        white-space:pre-line;
        margin-top:5px;
    }

    .par-details td{
        width:50%;
        height:29mm;
        border-top:0;
        border-bottom:0;
    }

    .par-details p{
        margin:9px 0;
    }

    .par-details span{
        display:inline-block;
        min-width:24mm;
        border-bottom:1px solid #555;
        text-align:center;
        font-weight:800;
        color:#174f2c;
        padding:0 5px;
    }

    .approval{
        text-align:center;
    }

    .approval strong{
        text-decoration:underline;
    }

    .approval b,
    .par-signatures b{
        display:block;
        text-align:center;
        text-decoration:underline;
        font-size:10px;
    }

    .approval small,
    .par-signatures small{
        display:block;
        text-align:center;
        font-size:8.5px;
    }

    /* APPROVED BY + ISSUED BY SIGNATURES */
    .signature-space{
        width:100%;
        height:15mm;
        display:flex;
        align-items:center;
        justify-content:center;
        text-align:center;
        overflow:hidden;
    }

    .signature-space img{
        display:block;
        max-width:45mm;
        max-height:13mm;
        width:auto;
        height:auto;
        object-fit:contain;
        margin:auto;
    }

    .par-signatures td{
        width:50%;
        height:45mm;
    }

    .par-signatures h3{
        text-align:center;
        font-size:10px;
        margin:0 0 8px;
        font-weight:800;
    }

    .par-signatures p{
        margin:7px 0;
        font-size:10px;
    }

    /* RECEIVED BY SIGNATURE */
    .receiver-signature-row{
        width:100%;
        display:flex;
        align-items:center;
        gap:8px;
        margin:7px 0 9px;
        font-size:10px;
    }

    .receiver-signature-label{
        flex:0 0 auto;
        white-space:nowrap;
    }

    .signature-line{
        flex:1 1 auto;
        min-width:32mm;
        height:10mm;
        border-bottom:1px solid #555;
        display:flex;
        align-items:center;
        justify-content:center;
        text-align:center;
        overflow:hidden;
    }

    .signature-line img{
        display:block;
        max-width:38mm;
        max-height:9mm;
        width:auto;
        height:auto;
        object-fit:contain;
        margin:auto;
    }

    .par-signatures .office{
        text-align:center;
        margin-top:12px;
    }

    .replacement-note{
        font-size:9px;
        color:#555;
        margin:5px 0;
    }

    .par-receipt footer{
        display:flex;
        align-items:center;
        justify-content:space-between;
        margin-top:8px;
        font-size:9px;
    }

    .par-receipt footer strong{
        font-size:10px;
        letter-spacing:.2px;
    }

    .footer-badges{
        display:flex;
        align-items:center;
        gap:6px;
    }

    .footer-badges img{
        height:26px;
        width:auto;
    }

    .footer-badges-right{
        gap:8px;
    }

    .badge-fallback{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        height:22px;
        width:22px;
        border:1px solid #999;
        border-radius:50%;
        font-size:7px;
        font-weight:800;
        color:#174f2c;
    }

    @page{
        size:A4 portrait;
        margin:8mm;
    }

    @media print{
        body{
            margin:0!important;
            background:#fff!important;
        }

        .par-receipt{
            border:0;
            width:194mm;
            min-height:281mm;
            padding:6mm;
        }

        .no-print{
            display:none!important;
        }

        .signature-space{
            display:flex!important;
            align-items:center!important;
            justify-content:center!important;
        }

        .receiver-signature-row{
            display:flex!important;
            align-items:center!important;
        }

        .signature-line{
            display:flex!important;
            align-items:center!important;
            justify-content:center!important;
        }

        .signature-space img,
        .signature-line img{
            display:block!important;
            margin:auto!important;
        }
    }
</style>
