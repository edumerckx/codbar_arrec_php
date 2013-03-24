<?php

/**
 *
 * @author eduardo
 * @see docs/Codbar4-v28052004.pdf
 */
class Arrecadacao {

	/**
	 * Código de barras para arrecadação
	 * com 44 posições
	 *
	 */
	private $codigo_barras;

	/**
	 * Produto - sempre 8 para arrecadação
	 *
	 */
	private $produto;

	/**
	 * Segmento
	 *
	 */
	private $segmento;

	/**
	 * Indicador de valor - efetivo ou referência
	 *
	 */
	private $indicador_valor;

	/**
	 * Dígito verificador do código de barras
	 *
	 */
	private $dv;

	/**
	 * Valor
	 *
	 */
	private $valor;

	/**
	 * Identificação empresa
	 *
	 */
	private $id_empresa;

	/**
	 * Campo livre - de uso da empresa/orgão
	 *
	 */
	private $campo_livre;

	/**
	 * Lista de segmentos de acordo com
	 * o layout Febraban
	 *
	 */
	private $lista_segmentos;

	/**
	 * Lista de indicadores de valor
	 *
	 */
	private $lista_indicadores;


	/**
	 *
	 * @param $codigo_barras
	 */
	public function __construct($codigo_barras = '') {

		if ($codigo_barras != '') {
			self::setCodigoBarras($codigo_barras);
		}

		// carrega lista de segmentos
		$lista = array();
		$lista[1] = 'Prefeituras';
		$lista[2] = 'Saneamento';
		$lista[3] = 'Energia Elétrica e Gás';
		$lista[4] = 'Telecomunicações';
		$lista[5] = 'Órgãos Governamentais';
		$lista[6] = 'Carnes e Assemelhados ou demais Empresas/Órgãos que serão identificadas através do CNPJ';
		$lista[7] = 'Multas de trânsito';
		$lista[9] = 'Uso exclusivo do banco';
		$this->lista_segmentos = $lista;

		// carrega lista de indicadores de valor
		$ind = array();
		$ind[6] = 'Valor efetivo - Módulo 10';
		$ind[7] = 'Valor referência - Módulo 10';
		$ind[8] = 'Valor efetivo - Módulo 11';
		$ind[9] = 'Valor referência - Módulo 11';
		$this->lista_indicadores = $ind;
	}

	/**
	 * Valida o tamanho da string e tipo
	 * (carateres numéricos).
	 * Atribui $codigo_barras
	 *
	 * @param $codigo_barras
	 */
	public function setCodigoBarras($codigo_barras) {

		try {
			if (is_numeric($codigo_barras) && strlen($codigo_barras) == 44 && substr($codigo_barras, 0, 1) == 8) {
				$this->codigo_barras = $codigo_barras;
			} else {
				throw new Exception('Código de barras inválido');
			}
		} catch (Exception $e) {
			error_log($e->getMessage());
			return false;
		}
	}

	/**
	 * Efetua split dos campos do código de barras
	 *
	 */
	public function split($codigo_barras = '') {

		try {
			if ($codigo_barras != '') {
				self::setCodigoBarras($codigo_barras);
			}
			
			if (self::codigoBarrasValido()) {
			
				// produto
				$this->produto = substr($this->codigo_barras, 0, 1) . ' - Arrecadação';
	
				// segmento
				$seg = substr($this->codigo_barras, 1, 1);
				if (array_key_exists($seg, $this->lista_segmentos)) {
					$this->segmento = $this->lista_segmentos[$seg];
				} else {
					throw new Exception('Segmento inválido!');
				}
					
				// indicador de valor
				$ind = substr($this->codigo_barras, 2, 1);
				if (array_key_exists($ind, $this->lista_indicadores)) {
					$this->indicador_valor = $this->lista_indicadores[$ind];
				} else {
					throw new Exception('Indicador inválido!');
				}
					
				// dígito verificador
				$this->dv = substr($this->codigo_barras, 3, 1);
				
				// valor
				$valor = bcdiv(substr($this->codigo_barras, 4, 11), 100, 2);
				if (in_array($this->indicador_valor, array(8, 9))) {
					if ($valor > 0) {
						$this->valor = $valor;
					} else {
						throw new Exception('Valor inválido!');
					}
				} else {
					$this->valor = $valor;
				}
				 
				// identificação da empresa
				// obs.: de acordo com o layout, se usado o código febraban 
				// este campo terá 4 caracteres, caso contrário será utilizado
				// os 8 primeiros digitos do CNPJ da empresa, utilizando assim
				// as 4 primeiras posições do campo livre 
				// por padrão será utilizado campo com 4 dígitos
				$this->id_empresa = substr($this->codigo_barras, 15, 4);
				
				// campo livre
				$this->campo_livre = substr($this->codigo_barras, 18, 25);
			}
		} catch (Exception $e) {
			error_log($e->getMessage());
			return false;
		}
	}


	/**
	 * Verifica se o código de barras informado
	 * é válido
	 * Retorna booleano
	 *
	 */
	public function codigoBarrasValido() {

		try {
			if (!isset($this->codigo_barras)) {
				throw new Exception("Código de barras não informado!");
			}

			if (substr($this->codigo_barras, 0, 1) != 8) {
				throw new Exception("Código de barras inválido para arrecadação!");
			}

			// validar dv...

		} catch (Exception $e) {
			error_log($e->getMessage());
			return false;
		}
	}

	/**
	 * Retorna array onde indice/valor correspondem
	 * à descrição do campo/valor.
	 *
	 */
	public function getDadosCodigoBarras() {

		try {
			if (self::codigoBarrasValido()) {
				$retorno = array();
				$retorno['produto'] = $this->produto;
				$retorno['segmento'] = $this->segmento;
				$retorno['indicador_valor'] = $this->indicador_valor;
				$retorno['dv'] = $this->dv;
				$retorno['valor'] = $this->valor;
				$retorno['id_empresa'] = $this->id_empresa;
				$retorno['campo_livre'] = $this->campo_livre;

				return $retorno;
			}
		} catch (Exception $e) {
			error_log($e->getMessage());
			return false;
		}
	}
	
	/**
	 * Cálculo de dígito baseado no módulo 10
	 * 
	 * @param $valor
	 */
	private function calculoModulo10($valor) {
		
		$total = 0;
		$m = 2;
		
		for ($i = strlen($valor) - 1; $i >= 0; $i--) {
			$res = $valor[$i] * $m;
			$total += (bcdiv($res, 10) + ($res % 10));
			
			if ($m == 2) {
				$m = 1;
			} else {
				$m = 2;
			}
		}
		
		$resto = $total % 10;
		
		if ($resto == 0) {
			$dac = 0;
		} else {
			$dac = 10 - $resto;
		}
		
		return $dac;
	}
	
	/**
	 * Cálculo de dígito baseado no módulo 11
	 * 
	 * @param $valor
	 */
	private function calculoModulo11($valor) {
		
		$total = 0;
		$m = 2;
		
		for ($i = strlen($valor) - 1; $i >=0; $i--) {
			$total += $valor[$i] * $m;
			
			$m++;
			
			if ($m == 10) {
				$m = 2;
			}
		}
		
		$resto = $total % 11;
		
		if ($resto < 2) {
			$dac = 0;
		} else {
			$dac = 11 - $resto;
		}
	}

}