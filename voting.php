<?php
/*
* Plugin Name: Voting
* Description: Adds voting functionality to page. Requirements: page name -> "voting" | buttons for voting: class -> "voting-button", id -> 1, 2, 3 etc.
* Author: Tomasz Śliwiński
*/

//Add AJAX url
function add_ajaxurl() {
    if(is_page('voting')) {
        echo '<script type="text/javascript">
                var ajaxurl = "' . admin_url('admin-ajax.php') . '";
                </script>';
    }
}
add_action('wp_head', 'add_ajaxurl');

//Add modal voting popup
function add_modal() {
    if(is_page('voting')) {
        include 'voting.html';
    }
}
add_action( 'wp_footer', 'add_modal' );

//Load JS and CSS for voting
function load_scripts_and_styles() {
    if(is_page('voting')) {
        wp_enqueue_script('voting-js', plugins_url('voting.js',__FILE__ ), null, null, true);
        wp_enqueue_style('voting-css', plugins_url('voting.css',__FILE__ ));
    }
}
add_action('wp_enqueue_scripts', 'load_scripts_and_styles');

function validate_email($email) {
    $sanitized_email = filter_var($email, FILTER_SANITIZE_EMAIL);
    if ($email == $sanitized_email && filter_var($sanitized_email, FILTER_VALIDATE_EMAIL)) {
        return $email;
    } else return false;
}

function is_email_registered($email) {
    global $wpdb;
    $query = $wpdb->prepare( "SELECT *
    FROM voting_codes 
    WHERE email = %s",
    $email
);
    $result = $wpdb->get_row( $query );
    if($result) return $result;
    else return false;
}

function generate_code() {
    $code;
    global $wpdb;
    
    do {
        $code = rand(1000,9999);
        $query = $wpdb->prepare( "SELECT *
        FROM voting_codes 
        WHERE code = %d",
        $code
        );
        $result = $wpdb->get_row( $query );
    } while ($result);
    return $code;
}

function register_code($email, $code) {
    global $wpdb;

    $prepared_date = date( 'Y-m-d H:i:s' );
    $wpdb->insert(
        'voting_codes',
        array(
            'timestamp' => "{$prepared_date}",
            'email' => $email,
            'code' => $code,
        ),
        array(
            '%s',
            '%s',
            '%d',
        )
    );
}

function send_code() {
    $email = validate_email($_POST['email']);
    if($email){
        if(!is_email_registered($email)){
            $to = $email;
            $subject = 'Kod do głosowania';
            $headers = array('Content-Type: text/html; charset=UTF-8');
            $code = generate_code();
            $message = "Twój kod to: {$code}. Ten kod jest potrzebny do potwierdzenia swojego głosu w plebiscycie Złoty Wąs.";
    
            register_code($email, $code);
            wp_mail($to, $subject, $message, $headers);
            wp_send_json(["success" =>  true, "msg" => "Kod wysłany. Sprawdź skrzynkę e-mail. Jeżeli e-mail nie dotarł to zaczekaj chwilę lub sprawdź folder SPAM."]);
        } else wp_send_json(["success" =>  false, "msg" => "Kod dla tego adresu e-mail jest już zarejestrowany. Możesz głosować tylko raz."]);
    } else wp_send_json(["success" =>  false, "msg" => "Podany adres e-mail wygląda na niepoprawny."]);
}

add_action('wp_ajax_send_code', 'send_code');
add_action('wp_ajax_nopriv_send_code', 'send_code');

function validate_code($code) {
    $sanitized_code = filter_var($code, FILTER_SANITIZE_NUMBER_INT);
    if ($code == $sanitized_code && filter_var($sanitized_code, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1000, "max_range" => 9999]])) {
        return $code;
    } else return false;
}

function validate_vote($vote) {
    $sanitized_vote = filter_var($vote, FILTER_SANITIZE_NUMBER_INT);
    if ($vote == $sanitized_vote && filter_var($sanitized_vote, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 6]])) {
        return $vote;
    } else return false;
}

function is_code_registered($code) {
    global $wpdb;
    $query = $wpdb->prepare( "SELECT *
    FROM voting_codes 
    WHERE code = %d",
    $code
);
    $result = $wpdb->get_row( $query );
    if($result) return $result;
    else return false;
}

function was_code_used($vc_id) {
    global $wpdb;
    $query = $wpdb->prepare( "SELECT *
    FROM votes 
    WHERE vc_id = %d",
    $vc_id
);
    $result = $wpdb->get_row( $query );
    if($result) return $result;
    else return false;
}

function register_vote($vc_id, $vote) {
    global $wpdb;

    $prepared_date = date( 'Y-m-d H:i:s' );
    $wpdb->insert(
        'votes',
        array(
            'timestamp' => "{$prepared_date}",
            'vc_id' => $vc_id,
            'vote' => $vote,
        ),
        array(
            '%s',
            '%d',
            '%d',
        )
    );
}

function vote() {
    $code = validate_code($_POST['code']);
    $vote = validate_vote($_POST['vote']);

    if($vote) {
       if($code){
            if($voting_code = is_code_registered($code)){
                if(!was_code_used($voting_code -> id)) {
                $to = $voting_code -> email;
                $subject = 'Dziękujemy za wziącie udziału w głosowaniu';
                $headers = array('Content-Type: text/html; charset=UTF-8');
                $message = file_get_contents(__DIR__.'/mail.html');
        
                register_vote($voting_code -> id, $vote);
                wp_mail($to, $subject, $message, $headers); 
                wp_send_json(["success" =>  true, "msg" => "Dziękujemy za wzięcie udziału w głosowaniu."]);
                } else wp_send_json(["success" =>  false, "msg" => "Ten kod został już użyty do głosowania. Możesz głosować tylko raz."]);
            } else wp_send_json(["success" =>  false, "msg" => "Ten kod nie umożliwia głosowania. Sprawdź czy podany kod jest poprawny."]);
        } else wp_send_json(["success" =>  false, "msg" => "Podany kod wygląda na niepoprawny. Prawidłowy kod jest 4-cyfrowy."]); 
    } else wp_send_json(["success" =>  false, "msg" => "Oddany głos jest niepoprawny"]);
}

add_action('wp_ajax_vote', 'vote');
add_action('wp_ajax_nopriv_vote', 'vote');