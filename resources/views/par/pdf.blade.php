<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo e($par->par_number); ?></title>
    <?php echo $__env->make('par._receipt_styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <style>@page{margin:8mm}body{margin:0}.par-receipt{border:0;min-height:auto;padding:7mm}</style>
</head>
<body><?php echo $__env->make('par._receipt', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></body>
</html>