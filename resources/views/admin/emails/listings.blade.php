<!DOCTYPE html>
<html lang="en">

<head>
    <title>Starling Properties</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
</head>

<body id="kt_body" class="app-blank">
    <div class="d-flex flex-column flex-root" id="kt_app_root">
        <!--begin::Wrapper-->
        <div class="d-flex flex-column flex-column-fluid">

            <!--begin::Body-->
            <div class="scroll-y flex-column-fluid px-10 py-10" data-kt-scroll="true" data-kt-scroll-activate="true"
                data-kt-scroll-height="auto" data-kt-scroll-dependencies="#kt_app_header_nav"
                data-kt-scroll-offset="5px" data-kt-scroll-save-state="true"
                style="background-color:#D5D9E2; --kt-scrollbar-color: #d9d0cc; --kt-scrollbar-hover-color: #d9d0cc">

                <!--begin::Email template-->
                <style>
                html,
                body {
                    padding: 0;
                    margin: 0;
                    font-family: Inter, Helvetica, "sans-serif";
                }

                a:hover {
                    color: #009ef7;
                }
                </style>

                <div id="#kt_app_body_content"
                    style="background-color:#D5D9E2; font-family:Arial,Helvetica,sans-serif; line-height: 1.5; min-height: 100%; font-weight: normal; font-size: 15px; color: #2F3044; margin:0; padding:0; width:100%;">
                    <div style="background-color:#ffffff; padding: 35px 0 34px 0; border-radius: 24px; margin:40px auto; max-width: 600px;">
                        <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" height="auto" style="border-collapse:collapse">
                            <tbody>
                                <tr>
                                    <td align="center" valign="center" style="text-align:center; padding-bottom: 10px">
                                        <div style="margin-bottom:20px; text-align:left">
                                            <div style="font-size:14px; text-align:left; font-weight:500; margin:0 60px 0px 60px; font-family:Arial,Helvetica,sans-serif">
                                                <p style="color:#181C32; font-size: 18px; font-weight:600; margin-bottom:10px">
                                                    Hey ,
                                                </p>

                                            </div>
                                            <div style="display: flex; justify-content:center; flex-wrap: wrap; margin:0 40px 12px 40px">
                                                @if(count($listings) > 0)
                                                    @foreach($listings as $listing)
                                                        <div style="width:100%; margin:18px 0px; text-align:center;">
                                                            <img alt="" style="width:100%; border-radius:12px; margin-bottom:9px" src="{{ $listing->featuredImage() }}" />
                                                            <table style="width:100%;">
                                                                <tr>
                                                                    <td colspan="2" style="font-weight: bold; border-bottom:2px solid #DDDDDD;">
                                                                        {{ $listing->title }}
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td style="text-align:left; font-weight:bold;">
                                                                        Location:
                                                                    </td>
                                                                    <td style="text-align:right;">
                                                                        {{ $listing->location() }}
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td style="text-align:left; font-weight:bold;">
                                                                        Type:
                                                                    </td>
                                                                    <td style="text-align:right;">
                                                                    {{ $listing->category ? $listing->category->name : '' }} {{ $listing->prop_type ? $listing->prop_type->name : '' }}
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td style="text-align:left; font-weight:bold;">
                                                                        BUA:
                                                                    </td>
                                                                    <td style="text-align:right;">
                                                                        {{$listing->bua  }} Sqft
                                                                    </td>
                                                                </tr>
                                                                
                                                                <tr>
                                                                    <td style="text-align:left; font-weight:bold;">
                                                                        Ref No.:
                                                                    </td>
                                                                    <td style="text-align:right;">
                                                                        {{$listing->refno  }}
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td style="text-align:left; font-weight:bold;">
                                                                        Price:
                                                                    </td>
                                                                    <td style="text-align:right;">
                                                                        AED {{$listing->price  }}
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                            <hr>
                                                            <a href="#" target="_blank" style="background-color:#D3B879; border-radius:6px; display:inline-block; padding:6px 19px; color: #FFFFFF; font-size: 14px; font-weight:500; font-family:Arial,Helvetica,sans-serif">
                                                                View Details
                                                            </a>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                        <hr>
                                    </td>
                                </tr>

                                <tr>
                                    <td align="center" valign="center" style="font-size: 13px; text-align:center; padding: 0 10px 10px 10px; font-weight: 500; color: #A1A5B7; font-family:Arial,Helvetica,sans-serif">
                                        <table border="0" style="background:#000000;" width="100%" align="center" cellspacing="0" cellpadding="0">
                                            <tbody>
                                                <tr valign="bottom" bgcolor="#000000" height="18">
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td width="70%">
                                                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="padding-right:10px;padding-left:10px">
                                                            <tbody>
                                                                <tr>
                                                                    <td align="center" width="40%" valign="middle"><img align="center" style="border-radius:50%;border:2px solid white" src="{{ auth()->user()->profileImage() }}" width="100" height="100" class="CToWUd" data-bit="iit" jslog="138226; u014N:xr6bB; 53:WzAsMl0."></td>
                                                                    <td align="left" width="60%" valign="middle">
                                                                        <table width="100%" border="0" align="center" cellpadding="1" cellspacing="0" style="font-family:'TwCenMT','Montserrat',times;color:#ffffff;font-weight:500;line-height:23px;padding-right:10px;border-right:1px solid white">
                                                                            <tbody>
                                                                                <tr>
                                                                                    <td align="center" style="color:#ffffff;font-size:15px" nowrap="">{{ auth()->user()->name }}</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td align="center" style="color:#ffffff;font-size:13px" nowrap="">{{ auth()->user()->designation }}</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td align="center" style="color:#ffffff;font-size:13px">+{{ auth()->user()->phone }}</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td align="center" style="color:#ffffff;font-size:13px"><a style="color:#ffffff;text-decoration:none" href="mailto:{{ auth()->user()->email }}" target="_blank">{{ auth()->user()->email }}</a></td>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                    <td width="30%">
                                                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                            <tbody>
                                                                <tr>
                                                                    <td align="center" valign="middle"><img src="https://portal.starlingproperties.ae/public/assets/images/logo.png" width="70" align="absmiddle" class="CToWUd" data-bit="iit"></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                                <tr valign="baseline" bgcolor="#000000" height="18">
                                                    <td></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>

                                <tr>
                                    <td align="center" valign="center" style="font-size: 13px; padding:0 15px; text-align:center; font-weight: 500; color: #A1A5B7;font-family:Arial,Helvetica,sans-serif">
                                        <p> &copy Copyright Starling Properties.
                                            <a href="https://starlingproperties.ae/unsubscribe" rel="noopener" target="_blank" style="font-weight: 600;font-family:Arial,Helvetica,sans-serif">Unsubscribe</a>&nbsp; from newsletter.
                                        </p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
</body>

</html>