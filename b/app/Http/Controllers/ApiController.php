<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Company;
use App\CompanyType;
use App\Wpuser;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class ApiController extends Controller
{
    //
    public function companytype($id) {
    	
		$company_types = CompanyType::find($id);

		if($company_types) {
			$company_types->companies;
			return $company_types;
		}else
			return response()->json(['message' => 'no resource was found'], 404);
    		
    }

    public function usercompanies($id, Request $request) {

        // $wpuser_compaines = Company::with("companytypes")->where('wpuser_id', $id)->get();        
        $wpuser_compaines = Wpuser::find($id)->companies()->with(["wpusers" => function($query) use($id) {
            $query->select("user_nicename")->where('wpuser_id', $id);
        }, "companytypes"])->get();      

        foreach ($wpuser_compaines as $key => $wpuser_company) {
            $wpuser_company->incorporation_date = date('d M Y', strtotime($wpuser_company->incorporation_date));
            $wpuser_company->wpusers[0]->pivot->renewal_date = date('d M Y', strtotime($wpuser_company->wpusers[0]->pivot->renewal_date));
        }

    	if(empty($wpuser_compaines)) {		
    		return response()->json(['message' => 'user not found'], 202)->setCallback($request->input('callback'));
    	}
        
        return response()->json(['message' => 'Success', 'companies' => $wpuser_compaines], 200)->setCallback($request->input('callback'));

    }

    public function usercompanydetails($id, $user_id, Request $request) {

        $wpuser_company_details = Company::with(['companytypes','companyshareholders', 'companydirectors', 'companysecretaries', 'servicescountries', 'informationservice', 'wpusers' => function($query) use($user_id) {
            $query->select("user_nicename")->where('wpuser_id', $user_id);
          }])->get()->find($id);

        // return $wpuser_company_details;

        $wpuser_company_details->wpusers[0]->pivot->renewal_date = date('d M Y', strtotime($wpuser_company_details->wpusers[0]->pivot->renewal_date));
        $wpuser_company_details->incorporation_date = date('d M Y', strtotime($wpuser_company_details->incorporation_date));

        if(!$wpuser_company_details) {
            return response()->json(['message' => 'company not found'], 202)->setCallback($request->input('callback'));
        }

        return response()->json(['message' => 'Success', 'companydetails' => $wpuser_company_details], 200)->setCallback($request->input('callback'));

    }

    public function uploadfiles(Request $request) {

        // $type = $request->type;

        if ($request->hasFile('files')) {
            $file = $request->file('files');   
            $destinationPath = public_path() . "/uploads/";

            $orgFilename     = $file->getClientOriginalName();
            $filename        = str_random(6) . '_' . $file->getClientOriginalName();
            $uploadSuccess   = $file->move($destinationPath, $filename);
        }

        if(!empty($uploadSuccess)) {
            error_log("Destination: $destinationPath");
            error_log("Filename: $filename");
            error_log("Extension: ".$file->getClientOriginalExtension());
            error_log("Original name: ".$file->getClientOriginalName());
            error_log("Real path: ".$file->getRealPath());
            return response()->json([
                "file" => [
                    "name" => $filename,
                    "org_name" => $orgFilename,
                    "destinationPath" => $destinationPath              
                ]
            ]);

            // return $filename . '||' . $orgFilename . '||' . $destinationPath;
        }
        else {
            error_log("Error moving file: ".$file->getClientOriginalName());
            return 'Erorr on ';
        }          

    }

    public function retrievesavedcompany(Request $request) {
        
        $user_id = $request->user_id;
        $company_id = $request->company_id;

        $wpuser_compaines = Company::with(['companytypes','companyshareholders', 'companydirectors', 'companysecretaries', 'servicescountries', 'informationservice', 'wpusers' => function($query) use($user_id) {
          $query->select('user_nicename')->where('wpuser_id', $user_id);
        }])->where('id', $company_id)->where('status', 0)->get(); //->where('wpuser_id', $user_id)

        if(empty($wpuser_compaines)) {      
            return response()->json(['message' => 'company not found'], 202)->setCallback($request->input('callback'));
        }
        
        return response()->json(['message' => 'Success', 'companies' => $wpuser_compaines], 200);

    }

    public function gettimezonelist($country) {

        $country_codes = array (
          'AF' => 'Afghanistan',
          'AX' => 'Åland Islands',
          'AL' => 'Albania',
          'DZ' => 'Algeria',
          'AS' => 'American Samoa',
          'AD' => 'Andorra',
          'AO' => 'Angola',
          'AI' => 'Anguilla',
          'AQ' => 'Antarctica',
          'AG' => 'Antigua and Barbuda',
          'AR' => 'Argentina',
          'AU' => 'Australia',
          'AT' => 'Austria',
          'AZ' => 'Azerbaijan',
          'BS' => 'Bahamas',
          'BH' => 'Bahrain',
          'BD' => 'Bangladesh',
          'BB' => 'Barbados',
          'BY' => 'Belarus',
          'BE' => 'Belgium',
          'BZ' => 'Belize',
          'BJ' => 'Benin',
          'BM' => 'Bermuda',
          'BT' => 'Bhutan',
          'BO' => 'Bolivia',
          'BA' => 'Bosnia and Herzegovina',
          'BW' => 'Botswana',
          'BV' => 'Bouvet Island',
          'BR' => 'Brazil',
          'IO' => 'British Indian Ocean Territory',
          'BN' => 'Brunei Darussalam',
          'BG' => 'Bulgaria',
          'BF' => 'Burkina Faso',
          'BI' => 'Burundi',
          'KH' => 'Cambodia',
          'CM' => 'Cameroon',
          'CA' => 'Canada',
          'CV' => 'Cape Verde',
          'KY' => 'Cayman Islands',
          'CF' => 'Central African Republic',
          'TD' => 'Chad',
          'CL' => 'Chile',
          'CN' => 'China',
          'CX' => 'Christmas Island',
          'CC' => 'Cocos (Keeling) Islands',
          'CO' => 'Colombia',
          'KM' => 'Comoros',
          'CG' => 'Congo',
          'CD' => 'Zaire',
          'CK' => 'Cook Islands',
          'CR' => 'Costa Rica',
          'CI' => 'Côte D\'Ivoire',
          'HR' => 'Croatia',
          'CU' => 'Cuba',
          'CY' => 'Cyprus',
          'CZ' => 'Czech Republic',
          'DK' => 'Denmark',
          'DJ' => 'Djibouti',
          'DM' => 'Dominica',
          'DO' => 'Dominican Republic',
          'EC' => 'Ecuador',
          'EG' => 'Egypt',
          'SV' => 'El Salvador',
          'GQ' => 'Equatorial Guinea',
          'ER' => 'Eritrea',
          'EE' => 'Estonia',
          'ET' => 'Ethiopia',
          'FK' => 'Falkland Islands (Malvinas)',
          'FO' => 'Faroe Islands',
          'FJ' => 'Fiji',
          'FI' => 'Finland',
          'FR' => 'France',
          'GF' => 'French Guiana',
          'PF' => 'French Polynesia',
          'TF' => 'French Southern Territories',
          'GA' => 'Gabon',
          'GM' => 'Gambia',
          'GE' => 'Georgia',
          'DE' => 'Germany',
          'GH' => 'Ghana',
          'GI' => 'Gibraltar',
          'GR' => 'Greece',
          'GL' => 'Greenland',
          'GD' => 'Grenada',
          'GP' => 'Guadeloupe',
          'GU' => 'Guam',
          'GT' => 'Guatemala',
          'GG' => 'Guernsey',
          'GN' => 'Guinea',
          'GW' => 'Guinea-Bissau',
          'GY' => 'Guyana',
          'HT' => 'Haiti',
          'HM' => 'Heard Island and Mcdonald Islands',
          'VA' => 'Vatican City State',
          'HN' => 'Honduras',
          'HK' => 'Hong Kong',
          'HU' => 'Hungary',
          'IS' => 'Iceland',
          'IN' => 'India',
          'ID' => 'Indonesia',
          'IR' => 'Iran, Islamic Republic of',
          'IQ' => 'Iraq',
          'IE' => 'Ireland',
          'IM' => 'Isle of Man',
          'IL' => 'Israel',
          'IT' => 'Italy',
          'JM' => 'Jamaica',
          'JP' => 'Japan',
          'JE' => 'Jersey',
          'JO' => 'Jordan',
          'KZ' => 'Kazakhstan',
          'KE' => 'KENYA',
          'KI' => 'Kiribati',
          'KP' => 'Korea, Democratic People\'s Republic of',
          'KR' => 'Korea, Republic of',
          'KW' => 'Kuwait',
          'KG' => 'Kyrgyzstan',
          'LA' => 'Lao People\'s Democratic Republic',
          'LV' => 'Latvia',
          'LB' => 'Lebanon',
          'LS' => 'Lesotho',
          'LR' => 'Liberia',
          'LY' => 'Libyan Arab Jamahiriya',
          'LI' => 'Liechtenstein',
          'LT' => 'Lithuania',
          'LU' => 'Luxembourg',
          'MO' => 'Macao',
          'MK' => 'Macedonia, the Former Yugoslav Republic of',
          'MG' => 'Madagascar',
          'MW' => 'Malawi',
          'MY' => 'Malaysia',
          'MV' => 'Maldives',
          'ML' => 'Mali',
          'MT' => 'Malta',
          'MH' => 'Marshall Islands',
          'MQ' => 'Martinique',
          'MR' => 'Mauritania',
          'MU' => 'Mauritius',
          'YT' => 'Mayotte',
          'MX' => 'Mexico',
          'FM' => 'Micronesia, Federated States of',
          'MD' => 'Moldova, Republic of',
          'MC' => 'Monaco',
          'MN' => 'Mongolia',
          'ME' => 'Montenegro',
          'MS' => 'Montserrat',
          'MA' => 'Morocco',
          'MZ' => 'Mozambique',
          'MM' => 'Myanmar',
          'NA' => 'Namibia',
          'NR' => 'Nauru',
          'NP' => 'Nepal',
          'NL' => 'Netherlands',
          'AN' => 'Netherlands Antilles',
          'NC' => 'New Caledonia',
          'NZ' => 'New Zealand',
          'NI' => 'Nicaragua',
          'NE' => 'Niger',
          'NG' => 'Nigeria',
          'NU' => 'Niue',
          'NF' => 'Norfolk Island',
          'MP' => 'Northern Mariana Islands',
          'NO' => 'Norway',
          'OM' => 'Oman',
          'PK' => 'Pakistan',
          'PW' => 'Palau',
          'PS' => 'Palestinian Territory, Occupied',
          'PA' => 'Panama',
          'PG' => 'Papua New Guinea',
          'PY' => 'Paraguay',
          'PE' => 'Peru',
          'PH' => 'Philippines',
          'PN' => 'Pitcairn',
          'PL' => 'Poland',
          'PT' => 'Portugal',
          'PR' => 'Puerto Rico',
          'QA' => 'Qatar',
          'RE' => 'Réunion',
          'RO' => 'Romania',
          'RU' => 'Russian Federation',
          'RW' => 'Rwanda',
          'SH' => 'Saint Helena',
          'KN' => 'Saint Kitts and Nevis',
          'LC' => 'Saint Lucia',
          'PM' => 'Saint Pierre and Miquelon',
          'VC' => 'Saint Vincent and the Grenadines',
          'WS' => 'Samoa',
          'SM' => 'San Marino',
          'ST' => 'Sao Tome and Principe',
          'SA' => 'Saudi Arabia',
          'SN' => 'Senegal',
          'RS' => 'Serbia',
          'SC' => 'Seychelles',
          'SL' => 'Sierra Leone',
          'SG' => 'Singapore',
          'SK' => 'Slovakia',
          'SI' => 'Slovenia',
          'SB' => 'Solomon Islands',
          'SO' => 'Somalia',
          'ZA' => 'South Africa',
          'GS' => 'South Georgia and the South Sandwich Islands',
          'ES' => 'Spain',
          'LK' => 'Sri Lanka',
          'SD' => 'Sudan',
          'SR' => 'Suriname',
          'SJ' => 'Svalbard and Jan Mayen',
          'SZ' => 'Swaziland',
          'SE' => 'Sweden',
          'CH' => 'Switzerland',
          'SY' => 'Syrian Arab Republic',
          'TW' => 'Taiwan, Province of China',
          'TJ' => 'Tajikistan',
          'TZ' => 'Tanzania, United Republic of',
          'TH' => 'Thailand',
          'TL' => 'Timor-Leste',
          'TG' => 'Togo',
          'TK' => 'Tokelau',
          'TO' => 'Tonga',
          'TT' => 'Trinidad and Tobago',
          'TN' => 'Tunisia',
          'TR' => 'Turkey',
          'TM' => 'Turkmenistan',
          'TC' => 'Turks and Caicos Islands',
          'TV' => 'Tuvalu',
          'UG' => 'Uganda',
          'UA' => 'Ukraine',
          'AE' => 'United Arab Emirates',
          'GB' => 'United Kingdom',
          'US' => 'United States',
          'UM' => 'United States Minor Outlying Islands',
          'UY' => 'Uruguay',
          'UZ' => 'Uzbekistan',
          'VU' => 'Vanuatu',
          'VE' => 'Venezuela',
          'VN' => 'Viet Nam',
          'VG' => 'Virgin Islands, British',
          'VI' => 'Virgin Islands, U.S.',
          'WF' => 'Wallis and Futuna',
          'EH' => 'Western Sahara',
          'YE' => 'Yemen',
          'ZM' => 'Zambia',
          'ZW' => 'Zimbabwe',
        );

        $country_code = array_search(strtolower($country), array_map('strtolower', $country_codes));

        $timezone = timezone_identifiers_list(4096, $country_code);
        $timezonestr = $timezone[0];
        date_default_timezone_set($timezonestr);
        $date= "Present time in ".$country.": ". date('D, d M Y H:i') . " hrs";

        return $date;
    }

}
