<link rel="stylesheet" href="{{ asset( 'admin/css/dashlite.min.css' ) }}">
<body class="nk-body bg-white npc-default pg-error">
    <div class="nk-wrap nk-wrap-nosidebar">
        <!-- content @s -->
        <div class="nk-content ">
            <div class="nk-block nk-block-middle wide-xs mx-auto">
                <div class="nk-block-content nk-error-ld text-center">
                    <h1 class="nk-error-head">500</h1>
                    <h3 class="nk-error-title">Server Error</h3>
                    <p class="nk-error-text">We are very sorry for inconvenience. It looks like like some how our server is crashed.</p>
                    <a href="{{ route( 'admin.module_parent.dashboard' ) }}" class="btn btn-lg btn-primary mt-2">Back To Home</a>
                </div>
            </div><!-- .nk-block -->
        </div>
        <!-- wrap @e -->
    </div>
</body>