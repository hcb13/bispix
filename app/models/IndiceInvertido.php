<?php

class IndiceInvertido extends Eloquent{

	protected $table = 'indice';
	
	private static function listaDiretorio($nomeDiretorio)
	{
		
		$lista = scandir($nomeDiretorio);
		$chave = array_search(".", $lista);
		unset($lista[$chave]);
		$chave = array_search("..", $lista);
		unset($lista[$chave]);

		return $lista;
	}	
	public static function quebraPalavras($nomeDiretorio)
	{
		$endAbsoluto = app_path().'/data/colecoes/'.$nomeDiretorio;
		$lista = self::listaDiretorio($endAbsoluto);

		$log = app_path().'/data/colecoes/log.txt';

		foreach($lista as $arq){
			
			$logFile = fopen($log,'w');
			$data = "Indexando o arquivo ".$arq." ...";
			fwrite($logFile, $data);
			fclose($logFile);

			$pont = fopen($endAbsoluto.'/'.$arq,'r');
			if($pont){
				$posicao = 0;
				while(true) {
					$linha = fgets($pont);
					if ($linha==null) break;

					$termos = explode(' ', $linha);
						
					foreach($termos as $t){

						$tripla = new IndiceInvertido;
						$valor = trim($t);
						
						if( strlen($valor) ){
							$posicao++;
							$tripla->termo = $t;
							$tripla->documento = $arq;
							$tripla->posicao = $posicao;
							$tripla->save();
						}
					}
				}
				fclose($pont);
			}
		}

		//Limpando o log do arquivo
		$logFile = fopen($log,'w');
		$data = "";
		fwrite($logFile, $data);
		fclose($logFile);
	}
	public static function preprocessamentoAlgoritmo()
	{
		$triplas = self::all();

		foreach ($triplas as $t) {
			$termo = $t->termo;
			$termo = self::normalizar($termo);
			
			$t->termo = $termo;
			$t->save();
		}
	}
	public static function normalizar($termo)
	{
		$simbolosRemocao = 
			array(
				"?", "!", ",", ";", "(", 
				")", "\"", ":", "."
			);
		$termoNormalizado = str_replace($simbolosRemocao, "", $termo);
		$termoNormalizado = mb_strtolower($termoNormalizado);

		return $termoNormalizado;
	}
	public static function bancoPronto()
	{
		$nomeTabela = (new self)->getTable();

		if( Schema::hasTable($nomeTabela) ){
			$val = self::all();
			if(count($val) == 0) 
				return true;
		}
		return false;		
	}
	public static function parametros($nomeMetodo)
	{
		switch($nomeMetodo){
			case 'index':
				return self::parametrosIndex();
				break;
			case 'preprocessamento':
				return self::parametrosPreprocessamento();
				break;
		}
	}
	private static function parametrosIndex()
	{
		$data['viewName'] = 'site.gerar-indice';
		$data['panelName'] = 'site.colecao.index';
		$data['scriptName'] = 'site.colecao.script';

		$data['navAtivo'] = 'colecoes';
		$data['panelUrl'] = URL::to('/gerar-indice/tokenizer');
        $data['panelId'] = 'colecaoForm';
        $data['panelNext'] = 'Próximo';
        $data['panelIcon'] = 'forward';

        return $data;
	}
	private static function parametrosPreprocessamento()
	{
        $data['viewName'] = 'site.gerar-indice';
		$data['panelName'] = 'site.preprocessamento.index';
		$data['scriptName'] = 'block.script';

		$data['navAtivo'] = 'preprocessamento';
		$data['panelUrl'] = URL::to('/gerar-indice/pre-processamento-2');
        $data['panelId'] = 'preprocessamentoForm';
        $data['panelNext'] = 'Próximo';
        $data['panelIcon'] = 'forward';

        return $data;
	}
}