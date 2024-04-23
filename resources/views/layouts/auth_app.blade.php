<!DOCTYPE html>
<html lang="en">
<head>
    <title>Starling Properties</title>
    <meta charset="utf-8" />
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta property="og:locale" content="en_US" />
    <meta property="og:type" content="article" />
    <meta property="og:title" content="Starling Properties" />
    <meta property="og:url" content="https://starlingproperties.ae" />
    <meta property="og:site_name" content="Starling Properties" />
    <link rel="canonical" href="{{route('login')}}" />
    <link rel="shortcut icon" href="{{ asset('assets/media/logos/favicon.ico') }}" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    @notifyCss
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    
    <script>
        if (window.top != window.self) {
            window.top.location.replace(window.self.location.href);
        }
    </script>
</head>
<body id="kt_body" class="auth-bg bgi-size-cover bgi-attachment-fixed bgi-position-center bgi-no-repeat">
    <script>
    var defaultThemeMode = "light";
    var themeMode;

    if (document.documentElement) {
        if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
            themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
        } else {
            if (localStorage.getItem("data-bs-theme") !== null) {
                themeMode = localStorage.getItem("data-bs-theme");
            } else {
                themeMode = defaultThemeMode;
            }
        }

        if (themeMode === "system") {
            themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
        }

        document.documentElement.setAttribute("data-bs-theme", themeMode);
    }
    </script>
    
    <div class="d-flex flex-column flex-root">
        <style>
            body {
                background-image: url({{asset('assets/media/auth/bg4.jpg')}});
            }

            [data-bs-theme="dark"] body {
                background-image: url({{asset('assets/media/auth/bg4-dark.jpg')}});
            }
        </style>

        <div class="d-flex flex-column flex-column-fluid flex-lg-row">
            <div class="d-flex flex-center w-lg-50 pt-15 pt-lg-0 px-10">
                <div class="d-flex flex-center flex-lg-start flex-column">
                    <a href="{{ route('login') }}" class="mb-7">
                        <img alt="Logo" src="{{ asset('assets/media/logos/logo.webp') }}" width="150" />
                    </a>

                    <p class="text-white fw-normal m-0 fs-2">
                        Luxury Real Estate Brokerage In Dubai,
                    </p>

                    <p class="text-white fw-normal m-0 fs-2">Choose From Dubai's Finest Luxury Properties.
                    </p>
                </div>
            </div>
            <div class="d-flex flex-column-fluid flex-lg-row-auto justify-content-center justify-content-lg-end p-12 p-lg-20">
                @yield('content')
            </div>
        </div>
    </div>


    <script>
        var hostUrl = <?= route('login'); ?>;
    </script>

    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>

    <script src="{{ asset('assets/js/custom/authentication/sign-in/general.js') }}"></script>
    <x-notify::notify />
    @notifyJs
</body>

</html>