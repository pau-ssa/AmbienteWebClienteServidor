<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\HttpException;
use App\Core\View;
use App\Models\Documento;
use App\Models\Libro;
use App\Services\ArchivoStorage;
use DomainException;
use PDO;
final class DocumentoController {
    private Documento $documentos;
    private Libro $libros;
    public function __construct(PDO $db){$this->documentos=new Documento($db);
        $this->libros=new Libro($db);}
    private function buscar(): array {
        $id=identificador();$libro=$this->libros->obtener($id);
        $doc=$this->documentos->obtener($id);
        if(!$libro || !$doc) throw new HttpException(404,'Este libro no tiene un PDF disponible.');
        try{$ruta=ArchivoStorage::verificar($doc['nombre_interno']);}
        catch(DomainException $ex){throw new HttpException(404,'El archivo PDF no está disponible.');}
        return [$libro,$doc,$ruta];
    }
    public function descargar(): void {[$libro,$doc,$ruta]=$this->buscar();$this->enviar($ruta,false);}

    private function enviar(string $ruta,bool $integrado): void {
        if(headers_sent())throw new HttpException(500,'No fue posible enviar el documento.');
        $tam=(int)filesize($ruta);
        if($tam<=0)throw new HttpException(404,'Archivo PDF vacío o inválido.');
        $inicio=0;$fin=$tam-1;$estado=200;
        // Los visores de PDF pueden solicitar un solo rango de bytes.
        $rango=$_SERVER['HTTP_RANGE']??null;
        if($rango!==null && $integrado) {
            if(!is_string($rango) || !preg_match('/\Abytes=(\d*)-(\d*)\z/D',$rango,$coincide)
                || ($coincide[1]==='' && $coincide[2]==='')) {
                header('Content-Range: bytes */'.$tam);http_response_code(416);return;
            }
            if($coincide[1]===''){$longitud=(int)$coincide[2];$inicio=max(0,$tam-$longitud);}
            else {$inicio=(int)$coincide[1];}
            if($coincide[1]!=='' && $coincide[2]!=='')$fin=min($tam-1,(int)$coincide[2]);
            if($inicio>$fin || $inicio>=$tam){header('Content-Range: bytes */'.$tam);
                http_response_code(416);return;}
            $estado=206;
        }
        http_response_code($estado);
        header('Content-Type: application/pdf');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: no-store');
        header('Accept-Ranges: bytes');
        header('Content-Disposition: '.($integrado?'inline':'attachment').'; filename="documento.pdf"');
        if($estado===206)header('Content-Range: bytes '.$inicio.'-'.$fin.'/'.$tam);
        header('Content-Length: '.($fin-$inicio+1));
        $f=fopen($ruta,'rb');if($f===false)return;
        fseek($f,$inicio);
        $restante=$fin-$inicio+1;
        while($restante>0 && !feof($f)){
            $chunk=fread($f,min(65536,$restante));
            if($chunk===false || $chunk==='')break;
            echo $chunk;$restante-=strlen($chunk);
        }
        fclose($f);
    }
}
