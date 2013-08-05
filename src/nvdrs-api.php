<?php

include_once('./simple_html_dom.php');

class WisqarsApi {
	
	public $dataArray = [];
	protected $debugFlag = 1;
	protected $useCache = 1;
	
	//LIST OF STATES FOR THE National Violent Death Reporting System
	protected $states = array(
// 			"Alaska"=>"02",
// 			"Colorado"=>"08",
// 			"Georgia"=>"13",
// 			"Kentucky"=>"21",
// 			"Maryland"=>"24",
// 			"Massachusetts"=>"25",
// 			"New Jersey"=>"34",
// 			"New Mexico"=>"35",
// 			"North Carolina"=>"37",
// 			"Oklahoma"=>"40",
 			"Oregon"=>"41",
// 			"Rhode Island"=>"44",
// 			"South Carolina"=>"45",
// 			"Utah"=>"49",
// 			"Virginia"=>"51",
// 			"Wisconsin"=>"55"
	);
	
	//LIST OF YEARS WITH SOME AMOUNT OF DATA.  NOTICE THAT THERE ARE MORE YEARS AVAILABLE THEN THE FRONT END LETS ON
	protected $years = array(2003,2004,2005,2006,2007,2008,2009,2010);

	//LIST OF AGES AVAILABLE
	//TODO: THIS CAN ALSO BE A SPECIFIC RANGE FOR BETTER RESOLUTION
	protected $ages = array(
			"10-14"=>2,
			"15-19"=>3,
			"20-24"=>4,
			"25-29"=>5,
			"30-34"=>6,
			"35-39"=>7,
			"40-44"=>8,
			"45-49"=>9,
			"50-54"=>10,
			"55-59"=>11,
			"60-64"=>12,
			"65-69"=>13,
			"70-74"=>14,
			"75-79"=>15,
			"80-84"=>16,
			"85-89"=>17,
			"Unknown"=>18
	);


	
	
	public function __construct() {
		//$this->data_array = (array) $arg1;

	}
	
	protected function _cacheName() {

		$filename = "../caches/cache.txt";
		return $filename;
	}
	
	protected function _writeCache() {
	
		file_put_contents($this->_cacheName(), json_encode($this->dataArray));
	}

	protected function _getMatchingFromCache() {
	
		#DOES THE CACHE FILE EXIST?
		if (file_exists($this->_cacheName())) {
			$this->dataArray = json_decode(file_get_contents($this->_cacheName() ), true);
			return 1;
		} else {
			return 0;
		}
	}


		
	public function getData() {
		
		foreach($this->years as $year) {
			foreach($this->states as $state => $stateId) {
			
				foreach($this->ages as $age => $ageId) {
			
					$deathCount = $this->getDeathCount($year, $stateId, $ageId);
					print "COUNT IN $state ($stateId) IN THE YEAR $year = ${deathCount} FOR $age ($ageId)<br>";
					$this->dataArray[$year][$state][$age]=$deathCount;
				}
			}
		}
		
		$this->_writeCache();
	}

	protected function getDeathCount($year, $stateId, $ageId) {

		$http_server = "http://wisqars.cdc.gov:8080/nvdrs/nvdrsController.jsp";
		
		//$post_variables = "down1=value1&down2=value2&across1=value3&across2=value4";
		$post_variables = "q1s1=4&traits1=0&q2s1=0&q3s5=1&q5s=0&q5s1i1=0&q6s1=${year}&";


// 		POST http://wisqars.cdc.gov:8080/nvdrs/nvdrsController.jsp Load Flags[LOAD_DOCUMENT_URI  LOAD_INITIAL_DOCUMENT_URI  ] Content Size[-1] Mime Type[text/html]
// 		Request Headers:
// 		Host[wisqars.cdc.gov:8080]
// 		User-Agent[Mozilla/5.0 (Windows NT 6.2; WOW64; rv:22.0) Gecko/20100101 Firefox/22.0]
// 		Accept[text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8]
// 		Accept-Language[en-US,en;q=0.5]
// 		Accept-Encoding[gzip, deflate]
// 		Referer[http://wisqars.cdc.gov:8080/nvdrs/nvdrsDisplay.jsp]
// 		Cookie[JSESSIONID=1D1C147D7CD5C4226F2540ECE886E7DE; s_vi=[CS]v1|28FF59A18514A734-60000180C0264F2C[CE]; fsr.r.session.11.2.0=%7B%22d%22%3A30%2C%22i%22%3A%22d036702-53533301-568d-89d8-ce3e3%22%2C%22e%22%3A1376252923353%7D; s_ria=Flash%2011%7C; ForeseeLoyalty_MID_soL8o3iA4g=2; s_cc=true; s_sq=%5B%5BB%5D%5D; s_ppv=56; fsr.s.session.11.2.0={"v":1,"rid":"d036702-53533301-568d-89d8-ce3e3","ru":"http://www.google.com/url?sa=t&rct=j&q=&esrc=s&source=web&cd=1&ved=0CC0QFjAA&url=http://www.cdc.gov/nchs/fastats/suicide.htm&ei=irL-Ue27HYqyiQKaooGABA&usg=AFQjCNFCx5q5TtoDKofLGBFHN2NtV0C-tA&sig2=APpYrqexQ5neDwUzJhlZuA&bvm=bv.50165853,d.cGE","r":"www.google.com","st":"","cp":{"GovDelivery":"N","Injury":"Y"},"to":5,"c":"http://www.cdc.gov/violenceprevention/suicide/datasources.html","pv":15,"lc":{"d0":{"v":15,"s":true}},"cd":0,"f":1375653402658,"sd":0,"i":1,"l":"en","s":1}]
// 		Connection[keep-alive]
// 		Post Data:
// 		q1s1[4]
// 		traits1[0]
// 		q2s1[0]
// 		q3s5[1]
// 		q5s[0]
// 		q5s1i1[0]
// 		q6s1[2005]
// 		q6s[0] or 1?
		//$post_variables = $post_variables."q6s=1&q6s2=${stateId}&q6s4=0&q6s5=0&q6s6=0&q6s10=0&q6s11=0&q6s12=0&q6s13=0&q6s15=0&q7s1=1&";
		$post_variables = $post_variables."q6s=1&q6s3=${stateId}&q6s4=0&q6s5=0&q6s6=0&q6s10=0&q6s11=0&q6s12=0&q6s13=0&q6s15=0&q7s1=1&";
// 		q6s2[16] or 41
// 		q6s4[0]
// 		q6s5[0]
// 		q6s6[0]
// 		q6s10[0]
// 		q6s11[0]
// 		q6s12[0]
// 		q6s13[0]
// 		q6s15[0]
// 		SubmitCmd[Submit+Request]
// 		q7s1[1]
		$post_variables = $post_variables."q7s2i1=${ageId}&q7s2i2=${ageId}&outputType=HTML&SubmitCmd=Submit+Request";
// 		q7s2i1[2] or 3 START AGE
// 		q7s2i2[2] or 3 END AGE
// 		outputType[HTML]
// 		Response Headers:
// 		Content-Type[text/html;charset=ISO-8859-1]
// 		Transfer-Encoding[chunked]
// 		Date[Sun, 04 Aug 2013 22:10:31 GMT]
// 		Server[NA]
		
		
		$ch = curl_init();
	
		#DONT ECHO THE CURL RETURN
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_URL,$http_server);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_variables);
	
		$contents = curl_exec($ch);
		curl_close($ch);
		//fclose($fp);
	
		$html = str_get_html($contents);
		
		//TODO
		//foreach($html->find('table.table',0) as $table) {			
		$table = $html->find('table.table',0);
			
		
		$rowCounter=0;
		foreach($table->find('tr') as $row) {

			if($rowCounter==1){
				$deathCount = trim($row->find('td',1)->plaintext);
			}
			
// 			foreach($row->find('td') as $cell) {
// 				echo "CELL = " .$cell." ROWCOUNT = $rowCounter<br>";
// 			}
			
			$rowCounter++;
		}

		return $deathCount;
		
	}
	
}

$wisqars = new WisqarsApi();
$wisqars->getData();