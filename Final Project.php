<?php
use Google\Cloud\Translate\V2\TranslateClient;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Google\Cloud\TextToSpeech\V1\TextToSpeechClient;
use Wikipedia\Wikipedia;
use IMDb\IMDb;

function get_bot_response($user_input)
{
    $processed_input = preprocess_text($user_input);

    if (strpos(strtolower($processed_input), "waktu") !== false) {
        return get_current_time_response();
    } elseif (preg_match("/hitung\s.*/i", $processed_input)) {
        return calculate_expression($processed_input);
    } elseif (preg_match("/cuaca\s.*/i", $processed_input)) {
        return get_weather_info($processed_input);
    } elseif (strpos(strtolower($processed_input), "sapa") !== false) {
        return greet_user();
    } elseif (strpos(strtolower($processed_input), "random") !== false) {
        return generate_random_number();
    } elseif (strpos(strtolower($processed_input), "berita") !== false) {
        return get_news_headlines();
    } elseif (strpos(strtolower($processed_input), "acak kata") !== false) {
        return scramble_word($processed_input);
    } elseif (strpos(strtolower($processed_input), "terjemahkan") !== false) {
        return terjemahkan_teks($processed_input);
    } elseif (strpos(strtolower($processed_input), "cari wikipedia") !== false) {
        return search_wikipedia($processed_input);
    } elseif (strpos(strtolower($processed_input), "kutipan inspiratif") !== false) {
        return get_inspirational_quote();
    } elseif (strpos(strtolower($processed_input), "cari film") !== false) {
        return get_movie_info($processed_input);
    } elseif (strpos(strtolower($processed_input), "fakta acak") !== false) {
        return get_random_fact();
    } elseif (strpos(strtolower($processed_input), "kirim email") !== false) {
        return send_email($user_input);
    } elseif (strpos(strtolower($processed_input), "acak angka") !== false) {
        return generate_random_number_range($processed_input);
    } elseif (strpos(strtolower($processed_input), "jumlah karakter") !== false) {
        return count_characters($processed_input);
    } elseif (strpos(strtolower($processed_input), "pilih dari") !== false) {
        return choose_from_list($processed_input);
    } else {
        return generate_default_response();
    }
}

function preprocess_text($text)
{
    $processed_text = strtolower($text);
    return $processed_text;
}

function get_current_time_response()
{
    Date_default_timezone_set('Asia/Jakarta');
    $now = new DateTime();
    $current_time = $now->format("H:i:s");
    return "Sekarang pukul " . $current_time;
}

function calculate_expression($expression)
{
    try {
        $calculation = substr($expression, 7);
        $result = eval($calculation);
        return "Hasil: " . $result;
    } catch (Exception $e) {
        return "Maaf, tidak dapat menghitung ekspresi matematika yang diberikan.";
    }
}

function get_weather_info($location)
{
    try {
        $location = substr($location, 6);
        $url = "http://api.openweathermap.org/data/2.5/weather?q=" . $location . "&appid=d3bd5653298c846ed1ccc141a1a77337";
        $response = file_get_contents($url);
        $data = json_decode($response, true);

        $weather = $data["weather"][0]["description"];
        $temperature = $data["main"]["temp"];
        $temperature = round($temperature - 273.15, 2); // Konversi dari Kelvin ke Celsius
        return "Cuaca di " . ucwords($location) . ": " . $weather . ", Suhu: " . $temperature . "°C";
    } catch (Exception $e) {
        return "Maaf, tidak dapat mendapatkan informasi cuaca untuk lokasi yang diberikan.";
    }
}

function greet_user()
{
    $greetings = ["Halo!", "Hai!", "Selamat datang!"];
    return $greetings[array_rand($greetings)];
}

function generate_random_number()
{
    $random_number = rand(1, 100);
    return "Angka acak: " . $random_number;
}

function get_news_headlines()
{
    $url = "https://newsapi.org/v2/top-headlines?country=id&apiKey=580184d4649c4946add425e4d136e221";
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    $articles = $data["articles"];

    $headlines = [];
    foreach ($articles as $article) {
        $headline = $article["title"];
        $headlines[] = $headline;
    }

    return "Berikut adalah beberapa headline berita terkini:\n" . implode("\n", $headlines);
}

function terjemahkan_teks($user_input)
{
    try {
        $teks_yang_akan_diterjemahkan = substr($user_input, 12);
        $translator = new TranslateClient();
        $terjemahan = $translator->translate($teks_yang_akan_diterjemahkan, [
            'target' => 'en'
        ]);
        return "Teks yang ingin diterjemahkan: " . $teks_yang_akan_diterjemahkan . "\nTeks yang telah diterjemahkan: " . $terjemahan['text'];
    } catch (Exception $e) {
        return "Maaf, tidak dapat menerjemahkan teks yang diberikan.";
    }
}

function search_wikipedia($user_input)
{
    try {
        $search_query = substr($user_input, 9);
        $wikipedia = new Wikipedia();
        $summary = $wikipedia->search($search_query);
        return "Hasil pencarian Wikipedia untuk '" . $search_query . "':\n" . $summary;
    } catch (Exception $e) {
        return "Maaf, tidak dapat menemukan informasi yang Anda cari di Wikipedia.";
    }
}

function get_inspirational_quote()
{
    try {
        $url = "https://zenquotes.io/api/random";
        $response = file_get_contents($url);
        $data = json_decode($response, true);
        $quote = $data[0]['q'];
        $author = $data[0]['a'];
        return "Kutipan Inspiratif:\n" . $quote . "\n- " . $author;
    } catch (Exception $e) {
        return "Maaf, tidak dapat memperoleh kutipan inspiratif saat ini.";
    }
}

function get_movie_info($user_input)
{
    try {
        $search_query = substr($user_input, 16);
        $ia = new IMDb();
        $movie = $ia->searchMovie($search_query)[0];
        $ia->getMovie($movie->getID());
        $title = $movie->getTitle();
        $year = $movie->getYear();
        $rating = $movie->getRating();
        $plot = $movie->getPlot()[0];
        return "Informasi Film:\nJudul: " . $title . "\nTahun: " . $year . "\nRating: " . $rating . "\nPlot: " . $plot;
    } catch (Exception $e) {
        return "Maaf, tidak dapat menemukan informasi film yang Anda cari.";
    }
}

function get_random_fact()
{
    try {
        $url = "https://uselessfacts.jsph.pl/random.json?language=id";
        $response = file_get_contents($url);
        $data = json_decode($response, true);
        $fact = $data['text'];

        return "Fakta Acak:\n" . $fact;
    } catch (Exception $e) {
        return "Maaf, tidak dapat memperoleh fakta acak saat ini.";
    }
}

function send_email($user_input)
{
    try {
        // ... kode pengiriman email ...

        return "Email berhasil dikirim!";
    } catch (Exception $e) {
        return "Maaf, gagal mengirim email.";
    }
}

function change_voice($user_input)
{
    try {
        $new_voice = substr($user_input, 12);
        $textToSpeechClient = new TextToSpeechClient([
            'credentials' => 'path/to/service-account-key.json'
        ]);
        $voices = $textToSpeechClient->listVoices();
        foreach ($voices as $voice) {
            if (strpos(strtolower($voice->getName()), strtolower($new_voice)) !== false) {
                // ... ubah suara ...

                return "Suara berhasil diubah menjadi " . $voice->getName();
            }
        }
        return "Maaf, suara yang diminta tidak tersedia.";
    } catch (Exception $e) {
        return "Maaf, gagal mengubah suara.";
    }
}

function speak_text($user_input)
{
    try {
        $text_to_speak = substr($user_input, 8);
        $textToSpeechClient = new TextToSpeechClient([
            'credentials' => 'path/to/service-account-key.json'
        ]);
        // ... kode untuk mengucapkan teks ...

        return "Teks berhasil diucapkan.";
    } catch (Exception $e) {
        return "Maaf, gagal mengucapkan teks.";
    }
}

function generate_random_number_range($user_input)
{
    try {
        $range_values = preg_match_all('/\d+/', $user_input, $matches);
        if (count($range_values) == 2) {
            $start_range = intval($range_values[0]);
            $end_range = intval($range_values[1]);
            $random_number = rand($start_range, $end_range);
            return "Angka acak antara " . $start_range . " dan " . $end_range . ": " . $random_number;
        } else {
            return "Format yang benar: 'acak angka <start> <end>'";
        }
    } catch (Exception $e) {
        return "Terjadi kesalahan dalam menghasilkan angka acak.";
    }
}

function count_characters($user_input)
{
    try {
        $text = substr($user_input, 16);
        $character_count = strlen($text);
        return "Jumlah karakter: " . $character_count;
    } catch (Exception $e) {
        return "Terjadi kesalahan dalam menghitung jumlah karakter.";
    }
}

function scramble_word($user_input)
{
    $word = substr($user_input, 10);
    $word_list = str_split($word);
    shuffle($word_list);
    $scrambled_word = implode("", $word_list);
    return "Kata yang diacak: " . $word . "\nKata yang teracak: " . $scrambled_word;
}

function choose_from_list($user_input)
{
    try {
        $options = explode(",", substr($user_input, 12));
        $selected_option = $options[array_rand($options)];
        return "Pilihan terpilih: " . trim($selected_option);
    } catch (Exception $e) {
        return "Terjadi kesalahan dalam memilih dari daftar.";
    }
}

function generate_default_response()
{
    $responses = [
        "Maaf saya tidak mengerti perintah Anda.",
    ];
    return $responses[array_rand($responses)];
}

echo '<html>';
echo '<head>';
echo '<title>Bot Final Project</title>';
echo '<style>';
echo 'body {';
echo '    font-family: Arial, sans-serif;';
echo '    background-color: #f2f2f2;';
echo '    margin: 0;';
echo '    padding: 0;';
echo '}';
echo '.container {';
echo '    max-width: 600px;';
echo '    margin: 0 auto;';
echo '    padding: 20px;';
echo '    background-color: #ffffff;';
echo '    box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);';
echo '    border-radius: 5px;';
echo '    margin-top: 50px;';
echo '}';
echo 'h1 {';
echo '    text-align: center;';
echo '}';
echo 'form {';
echo '    margin-top: 20px;';
echo '}';
echo 'input[type="text"] {';
echo '    width: 100%;';
echo '    padding: 10px;';
echo '    font-size: 16px;';
echo '    border: 1px solid #ccc;';
echo '    border-radius: 4px;';
echo '}';
echo 'input[type="submit"] {';
echo '    background-color: #4CAF50;';
echo '    color: #ffffff;';
echo '    border: none;';
echo '    padding: 10px 20px;';
echo '    font-size: 16px;';
echo '    cursor: pointer;';
echo '    border-radius: 4px;';
echo '}';
echo '.bot-response {';
echo '    margin-top: 20px;';
echo '    background-color: #f9f9f9;';
echo '    padding: 10px;';
echo '    border: 1px solid #ccc;';
echo '    border-radius: 4px;';
echo '}';
echo '</style>';
echo '</head>';
echo '<body>';
echo '<div class="container">';
echo '<h1>BedBot</h1>';
echo '<form method="POST" action="">';
echo '    <input type="text" name="user_input" placeholder="Masukkan pesan Anda..." required autofocus>';
echo '    <input type="submit" value="Kirim">';
echo '</form>';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_input = $_POST["user_input"];
    $bot_response = get_bot_response($user_input);
    echo '<div class="bot-response">';
    echo '<strong>Bot:</strong> ' . $bot_response;
    echo '</div>';
}
echo '</div>';
echo '</body>';
echo '</html>';
