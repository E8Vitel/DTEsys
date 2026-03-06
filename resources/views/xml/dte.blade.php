<?xml version="1.0" encoding="UTF-8"?>
<EnvioDTE xmlns="http://www.sii.cl/SiiDte" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sii.cl/SiiDte EnvioDTE_v10.xsd" version="1.0">
<SetDTE ID="SetDoc-{{ $dte->id }}">
 <Caratula version="1.0">
  <RutEmisor>{{ $dte->rut_emisor }}</RutEmisor>
  <RutEnvia>{{ $dte->user?->rut ?? $dte->rut_emisor }}</RutEnvia>
  <RutReceptor>60803000-K</RutReceptor>
  <FchResol>{{ \Carbon\Carbon::parse($dte->created_at)->format('Y-m-d') }}</FchResol>
  <NroResol>0</NroResol>
  <TmstFirmaEnv>{{ now()->format('Y-m-d\\TH:i:s') }}</TmstFirmaEnv>
  <SubTotDTE>
   <TpoDTE>{{ $dte->tipoDte?->codigo ?? 33 }}</TpoDTE>
   <NroDTE>1</NroDTE>
  </SubTotDTE>
 </Caratula>
 <DTE version="1.0">
  <Documento ID="DTE{{ preg_replace('/[^0-9]/', '', (string) $dte->folio) ?: $dte->id }}T{{ $dte->tipoDte?->codigo ?? 33 }}">
   <Encabezado>
    <IdDoc>
     <TipoDTE>{{ $dte->tipoDte?->codigo ?? 33 }}</TipoDTE>
     <Folio>{{ preg_replace('/[^0-9]/', '', (string) $dte->folio) ?: $dte->id }}</Folio>
     <FchEmis>{{ \Carbon\Carbon::parse($dte->fecha_emision)->format('Y-m-d') }}</FchEmis>
    </IdDoc>
    <Emisor>
     <RUTEmisor>{{ $dte->rut_emisor }}</RUTEmisor>
     <RznSoc>{{ $dte->razon_social_emisor }}</RznSoc>
     <GiroEmis>{{ $dte->user?->giro ?? 'GIRO NO INFORMADO' }}</GiroEmis>
     <DirOrigen>{{ $dte->user?->direccion ?? 'DIRECCION NO INFORMADA' }}</DirOrigen>
     <CmnaOrigen>{{ $dte->comuna?->nombre ?? 'SANTIAGO' }}</CmnaOrigen>
     <CiudadOrigen>{{ $dte->region?->nombre ?? 'SANTIAGO' }}</CiudadOrigen>
    </Emisor>
    <Receptor>
     <RUTRecep>{{ $dte->rut_receptor }}</RUTRecep>
     <RznSocRecep>{{ $dte->razon_social_receptor }}</RznSocRecep>
     <GiroRecep>{{ $dte->giro_receptor ?? 'GIRO NO INFORMADO' }}</GiroRecep>
     <DirRecep>{{ $dte->direccion_receptor ?? 'DIRECCION NO INFORMADA' }}</DirRecep>
     <CmnaRecep>{{ $dte->comuna?->nombre ?? 'SANTIAGO' }}</CmnaRecep>
     <CiudadRecep>{{ $dte->region?->nombre ?? 'SANTIAGO' }}</CiudadRecep>
    </Receptor>
    <Totales>
     <MntNeto>{{ (int) round($dte->monto_neto) }}</MntNeto>
     <TasaIVA>19</TasaIVA>
     <IVA>{{ (int) round($dte->iva) }}</IVA>
     <MntTotal>{{ (int) round($dte->monto_total) }}</MntTotal>
    </Totales>
   </Encabezado>
@foreach($dte->detalles as $index => $det)
   <Detalle>
    <NroLinDet>{{ $index + 1 }}</NroLinDet>
    <NmbItem><![CDATA[{{ $det->descripcion }}]]></NmbItem>
    <QtyItem>{{ (float) $det->cantidad }}</QtyItem>
    <PrcItem>{{ (int) round($det->precio_unitario) }}</PrcItem>
    <MontoItem>{{ (int) round($det->subtotal) }}</MontoItem>
   </Detalle>
@endforeach
   <TmstFirma>{{ now()->format('Y-m-d\\TH:i:s') }}</TmstFirma>
  </Documento>
 </DTE>
</SetDTE>
</EnvioDTE>
