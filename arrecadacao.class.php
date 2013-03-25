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
	 * @var string $codigo_barras
	 */
	private $codigo_barras;

	/**
	 * Linha digitável com 48 posições
	 *
	 * @var string $linha_digitavel
	 */
	private $linha_digitavel;

	/**
	 * Produto - sempre 8 para arrecadação
	 *
	 * @var string $produto
	 */
	private $produto;

	/**
	 * Segmento
	 *
	 * @var int $segmento
	 */
	private $segmento;
	
	/**
	 * Descrição para segmento
	 * 
	 * @var string $segmento_descricao
	 */
	private $segmento_descricao;

	/**
	 * Indicador de valor - efetivo ou referência
	 *
	 * @var int $indicador_valor
	 */
	private $indicador_valor;

	/**
	 * Descrição para indicador_valor
	 * 
	 * @var string $indicador_descricao
	 */
	private $indicador_descricao;
	
	/**
	 * Dígito verificador do código de barras
	 *
	 * @var int $dv
	 */
	private $dv;

	/**
	 * Valor
	 *
	 * @var double $valor
	 */
	private $valor;

	/**
	 * Identificação empresa
	 *
	 * @var int $id_empresa
	 */
	private $id_empresa;

	/**
	 * Campo livre - de uso da empresa/orgão
	 *
	 * @var string $campo_livre
	 */
	private $campo_livre;

	/**
	 * Lista de segmentos de acordo com
	 * o layout Febraban
	 *
	 * @var array $lista_segmentos
	 */
	private $lista_segmentos;

	/**
	 * Lista de indicadores de valor
	 *
	 * @var array $lista_indicadores
	 */
	private $lista_indicadores;


	/**
	 * Construtor
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

		return $this;
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
				
			return $this;
		} catch (Exception $e) {
			echo $e->getMessage();
			return false;
		}
	}

	/**
	 * Efetua split dos campos do código de barras
	 *
	 */
	private function split() {

		try {
			// produto
			$this->produto = substr($this->codigo_barras, 0, 1) . ' - Arrecadação';

			// segmento
			$seg = substr($this->codigo_barras, 1, 1);
			if (array_key_exists($seg, $this->lista_segmentos)) {
				$this->segmento = $seg;
				$this->segmento_descricao = $this->lista_segmentos[$seg];
			} else {
				throw new Exception('Segmento inválido!');
			}
				
			// indicador de valor
			$ind = substr($this->codigo_barras, 2, 1);
			if (array_key_exists($ind, $this->lista_indicadores)) {
				$this->indicador_valor = $ind;
				$this->indicador_descricao = $this->lista_indicadores[$ind];
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
				
			return $this;
		} catch (Exception $e) {
			echo $e->getMessage();
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

			self::split();
				
			$dv = $this->codigo_barras[3];
			$validar = '';
			for ($i = 0; $i < strlen($this->codigo_barras); $i++) {
				if ($i != 3) {
					$validar .= $this->codigo_barras[$i];
				}
			}

			if (in_array($this->indicador_valor, array(6, 7))) {
				// valida pelo módulo 10
				$val = self::calculoModulo10($validar);

				if ($val != $dv) {
					return false;
				}
				
				return true;
			} else if (in_array($this->indicador_valor, array(8, 9))) {
				// valida pelo módulo 11
				$val = self::calculoModulo11($validar);

				if ($val != $dv) {
					return false;
				}

				return true;
			}

			return false;
		} catch (Exception $e) {
			echo $e->getMessage();
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
				$retorno['segmento_descricao'] = $this->segmento_descricao;
				$retorno['indicador_valor'] = $this->indicador_valor;
				$retorno['indicador_descricao'] = $this->indicador_descricao;
				$retorno['dv'] = $this->dv;
				$retorno['valor'] = $this->valor;
				$retorno['id_empresa'] = $this->id_empresa;
				$retorno['campo_livre'] = $this->campo_livre;

				return $retorno;
			} 
		} catch (Exception $e) {
			echo $e->getMessage();
			return false;
		}
	}
	
	/**
	 * Recupera codigo_barras
	 * 
	 */
	public function getCodigoBarras() {
		
		try {
			if (!isset($this->codigo_barras)) {
				throw new Exception('Dados não informados!');
			}
		
			return $this->codigo_barras;
		} catch (Exception $e) {
			echo $e->getMessage();
			return false;
		}
	}

	/**
	 * Atribui valor para linha_digitavel
	 * e codigo_barras
	 *
	 * @param $linha_digitavel
	 */
	public function setLinhaDigitavel($linha_digitavel) {

		try {
			if (is_numeric($linha_digitavel) && strlen($linha_digitavel) == 48) {
				$this->linha_digitavel = $linha_digitavel;

				// seta o código de barras
				$cb = '';
				for ($i = 0; $i < strlen($linha_digitavel); $i++) {
					if (!in_array($i, array(11, 23, 35, 47))) {
						$cb .= $linha_digitavel[$i];
					}
				}

				self::setCodigoBarras($cb);
			} else {
				throw new Exception('Linha digitável inválida!');
			}
				
			return $this;
		} catch (Exception $e) {
			echo $e->getMessage();
			return false;
		}
	}

	/**
	 * Recupera linha_digitavel
	 *
	 */
	public function getLinhaDigitavel() {

		if (!isset($this->linha_digitavel)) {
			self::gerarLinhaDigitavel();
		}

		return $this->linha_digitavel;
	}

	/**
	 * A partir do codigo de barras gera a linha digitavel
	 *
	 */
	private function gerarLinhaDigitavel() {

		if ($this->indicador_valor == 6 || $this->indicador_valor == 7) {
			$mod = 10;
		} else {
			$mod = 11;
		}

		$campo1 = substr($this->codigo_barras, 0, 11);
		$campo2 = substr($this->codigo_barras, 11, 11);
		$campo3 = substr($this->codigo_barras, 22, 11);
		$campo4 = substr($this->codigo_barras, 33, 11);

		if ($mod == 10) {
			$campo1 = $campo1 . self::calculoModulo10($campo1);
			$campo2 = $campo2 . self::calculoModulo10($campo2);
			$campo3 = $campo3 . self::calculoModulo10($campo3);
			$campo4 = $campo4 . self::calculoModulo10($campo4);
		} else {
			$campo1 = $campo1 . self::calculoModulo11($campo1);
			$campo2 = $campo2 . self::calculoModulo11($campo2);
			$campo3 = $campo3 . self::calculoModulo11($campo3);
			$campo4 = $campo4 . self::calculoModulo11($campo4);
		}

		$this->linha_digitavel = $campo1 . $campo2 . $campo3 . $campo4;

		return $this;
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

		return $dac;
	}

}