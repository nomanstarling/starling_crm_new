<!DOCTYPE html>
<html lang="en">

<head>
    <title>Starling Properties</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
</head>

<body id="kt_body" class="app-blank" style="padding-top:50px; padding-bottom:50px;">
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
                    style="background-color:#D5D9E2; padding-top:50px; padding-bottom:50px; font-family:Arial,Helvetica,sans-serif; line-height: 1.5; min-height: 100%; font-weight: normal; font-size: 15px; color: #2F3044; margin:0; padding:0; width:100%;">
                    <div style="background-color:#ffffff; padding: 35px 0 34px 0; border-radius: 10px; margin-top:50px; margin-bottom:50px; margin-right:auto; margin-left:auto; max-width: 600px;">
                        <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" height="auto" style="border-collapse:collapse">
                            <tbody>
                                <tr>
                                    <td align="center" valign="center" style="text-align:center; padding-bottom: 10px">
                                        <div style="margin-bottom:20px; text-align:left">
                                            <div style="font-size:14px; text-align:left; font-weight:500; margin:0 60px 0px 60px; font-family:Arial,Helvetica,sans-serif">
                                                <p style="color:#181C32; font-size: 18px; font-weight:600; margin-bottom:10px">
                                                    Hi {{$recipent}}, These leads have been assigned to you by {{$user->name}},
                                                </p>
                                            </div>
                                            <ul style="list-style-type: none; padding: 0; margin: 0 40px 12px 40px;">
                                                @if(count($leads) > 0)
                                                    @foreach($leads as $lead)
                                                        <li style="border: 1px solid #E5E5E5; border-radius: 8px; margin-bottom: 12px; padding:6px 12px 6px 12px; background-color: #f9f9f9; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                                                            <span style="font-weight: bold;">Click on reference number to view the lead:</span> 
                                                            <a href="{{ route('leads.index') }}?refno={{ $lead->refno }}" style="text-decoration: none; color: #007BFF; margin-left: 8px;">
                                                                {{ $lead->refno }}
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                @else
                                                    <li style="border: 1px solid #ccc; border-radius: 8px; margin-bottom: 12px; padding: 12px; background-color: #f9f9f9; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                                                        No leads found.
                                                    </li>
                                                @endif
                                            </ul>

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
                                                                    <td align="center" width="40%" valign="middle"><img align="center" style="border-radius:50%;border:2px solid white" src="{{ $user->profileImage() }}" width="100" height="100" class="CToWUd" data-bit="iit" jslog="138226; u014N:xr6bB; 53:WzAsMl0."></td>
                                                                    <td align="left" width="60%" valign="middle">
                                                                        <table width="100%" border="0" align="center" cellpadding="1" cellspacing="0" style="font-family:'TwCenMT','Montserrat',times;color:#ffffff;font-weight:500;line-height:23px;padding-right:10px;border-right:1px solid white">
                                                                            <tbody>
                                                                                <tr>
                                                                                    <td align="center" style="color:#ffffff;font-size:15px" nowrap="">{{ $user->name }}</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td align="center" style="color:#ffffff;font-size:13px" nowrap="">{{ $user->designation }}</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td align="center" style="color:#ffffff;font-size:13px">+{{ $user->phone }}</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td align="center" style="color:#ffffff;font-size:13px"><a style="color:#ffffff;text-decoration:none" href="mailto:{{ $user->email }}" target="_blank">{{ $user->email }}</a></td>
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
                                        <p> © Copyright Starling Properties.
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