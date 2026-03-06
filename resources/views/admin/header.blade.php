<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="title" content="Sama Sama Oye!">
    <meta name="description" content="Malaysia’s first dedicated digital radio station celebrating the infectious energy of Bollywood and Punjabi music, created for all Malaysians and lovers of this genre.">
    <meta property="og:title" content="Sama Sama Oye!">
    <meta property="og:description" content="Malaysia’s first dedicated digital radio station celebrating the infectious energy of Bollywood and Punjabi music, created for all Malaysians and lovers of this genre.">

    <link rel="shortcut icon" href="{{ asset( 'admin/images/favicon.ico' ) . Helper::assetVersion() }}">

    @if ( @$header )
        <title>{{ @$header['title'] }} | {{ Helper::websiteName() }}</title>
    @else
        <title>{{ Helper::websiteName() }} Admin Panel</title>
    @endif

    <!-- StyleSheets  -->
    <link rel="stylesheet" href="{{ asset( 'admin/css/dashlite.min.css' . Helper::assetVersion() ) }}">
    <link rel="stylesheet" href="{{ asset( 'admin/css/extended.css' . Helper::assetVersion() ) }}">
    <link href="{{ asset( 'admin/css/flatpickr.min.css' ) . Helper::assetVersion() }}" rel="stylesheet">
    <link href="{{ asset( 'admin/css/select2.min.css' ) . Helper::assetVersion() }}" rel="stylesheet">
    <link href="{{ asset( 'admin/css/select2-bootstrap-5-theme.min.css' ) . Helper::assetVersion() }}" rel="stylesheet">
    <link href="{{ asset( 'admin/css/flatpickr-monthSelect.css' ) . Helper::assetVersion() }}" rel="stylesheet">

    <link href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    {{-- datatable row sortable --}}
    <link href="{{ asset( 'admin/css/rowReorder.datatable.min.css' ) . Helper::assetVersion() }}" rel="stylesheet">

    {{-- color picker --}}
    <link rel="stylesheet" href="{{ asset( 'admin/css/evol-colorpicker.css' ) . Helper::assetVersion() }}">
    <link rel="stylesheet" href="{{ asset( 'admin/css/evol-colorpicker.min.css' ) . Helper::assetVersion() }}">

</head>

<style>
    .dt-container {
        overflow: hidden;
    }
</style>