<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?php echo e($par->par_number); ?> · Property Acknowledgement Receipt</title>

    
    <?php echo $__env->make('par._receipt_styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <style>
        body{
            margin:0;
            background:#171c22;
            font-family:Arial,Helvetica,sans-serif;
        }

        .document-toolbar{
            position:sticky;
            top:0;
            z-index:5;
            display:flex;
            justify-content:space-between;
            align-items:center;
            padding:12px 20px;
            background:#11161d;
            color:#fff;
            border-bottom:1px solid #303947;
        }

        .document-actions{
            display:flex;
            gap:9px;
        }

        .document-actions a,
        .document-actions button{
            border:1px solid #556174;
            border-radius:5px;
            background:#1b2430;
            color:#fff;
            padding:9px 15px;
            font-weight:700;
            cursor:pointer;
            text-decoration:none;
        }

        .document-actions .primary{
            background:#b89418;
            border-color:#d0a91e;
            color:#fff;
        }

        .document-page{
            padding:22px;
        }

        /*
        |--------------------------------------------------------------------------
        | PDF / DOMPDF SAFE SIGNATURE STYLES
        |--------------------------------------------------------------------------
        |
        | DOMPDF has limited flexbox support. The browser preview can display
        | flex correctly, but signatures may disappear or move when the same
        | receipt is rendered as PDF.
        |
        | These rules intentionally use block / inline-block + text-align
        | instead of flexbox so the signatures render in both browser and PDF.
        |
        */

        .signature-space{
            width:100% !important;
            height:15mm !important;
            display:block !important;
            text-align:center !important;
            overflow:visible !important;
            line-height:15mm !important;
        }

        .signature-space img{
            display:inline-block !important;
            max-width:45mm !important;
            max-height:13mm !important;
            width:auto !important;
            height:auto !important;
            vertical-align:middle !important;
            object-fit:contain !important;
            margin:0 auto !important;
        }

        .receiver-signature-row{
            width:100% !important;
            display:block !important;
            margin:7px 0 9px !important;
            font-size:10px !important;
            white-space:nowrap;
        }

        .receiver-signature-label{
            display:inline-block !important;
            vertical-align:middle !important;
            margin-right:8px !important;
        }

        .signature-line{
            display:inline-block !important;
            width:38mm !important;
            height:10mm !important;
            border-bottom:1px solid #555 !important;
            text-align:center !important;
            vertical-align:middle !important;
            line-height:10mm !important;
            overflow:visible !important;
        }

        .signature-line img{
            display:inline-block !important;
            max-width:36mm !important;
            max-height:9mm !important;
            width:auto !important;
            height:auto !important;
            vertical-align:middle !important;
            object-fit:contain !important;
            margin:0 auto !important;
        }

        @media(max-width:640px){
            .document-toolbar{
                align-items:flex-start;
                gap:10px;
                flex-direction:column;
            }

            .document-page{
                padding:8px;
            }

            .document-actions{
                width:100%;
            }

            .document-actions > *{
                flex:1;
            }
        }

        @media print{
            body{
                margin:0 !important;
                background:#fff !important;
            }

            .document-toolbar{
                display:none !important;
            }

            .document-page{
                padding:0 !important;
            }

            /*
             * Keep PDF/print signature rendering non-flex.
             */
            .signature-space{
                display:block !important;
                text-align:center !important;
            }

            .signature-space img{
                display:inline-block !important;
                margin:0 auto !important;
            }

            .receiver-signature-row{
                display:block !important;
            }

            .signature-line{
                display:inline-block !important;
                text-align:center !important;
            }

            .signature-line img{
                display:inline-block !important;
                margin:0 auto !important;
            }
        }
    </style>
</head>

<body>

    <div class="document-toolbar no-print">
        <div>
            <strong>View PAR</strong> · <?php echo e($par->par_number); ?>

        </div>

        <div class="document-actions">
            <a href="<?php echo e(route('staff.par.pdf', $par)); ?>">
                Generate PDF
            </a>

            <button
                class="primary"
                type="button"
                onclick="window.print()"
            >
                Print PAR
            </button>
        </div>
    </div>

    <main class="document-page">
        <?php echo $__env->make('par._receipt', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </main>

</body>
</html>