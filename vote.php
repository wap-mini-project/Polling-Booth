<?php
include_once "header.php";
require_once "includes/dbh.inc.php";
require_once "includes/functions.inc.php";

if (!isLoggedIn($_SESSION)) {
    header("location: signup.php?error=usernotloggedin");
    exit();
}

if (!isset($_GET["id"])) {
    header("location: polls.php/error=errorloadingpoll");
    exit();
}

$id = (int)$_GET["id"];
$poll = fetchPoll($conn, $id);

if (!$poll) {
    header("location: polls.php?error=polldoesntexist");
    exit();
}

$choices = fetchChoices($conn, $id);

?>
<div>
    <?php
    echo '<div class="poll-center">';
    if (pollAnswered($conn, $_SESSION["userid"], $_GET["id"]) === true && pollExpired($poll) === true) {
        echo "<h2 style='color:#66fcf1;'>" . $poll["pollsQues"] . "</h2>";
        if ($poll["pollsDesc"]) {
            echo "<p>" . $poll["pollsDesc"] . "</p>";
        }
        echo "<h5 style='color:white;'>Poll answered - </h5>";
        $rows = number_of_choices($conn, $_GET["id"]);
        $sum = array_sum(array_column($rows, 'choice_count'));
        $selectedChoiceIds = array_column($rows, 'choicesId');
        $userSelectedChoice = selectedChoice($conn, $_SESSION["userid"], $_GET["id"])[0];
        foreach ($choices as $choice) {
            if (!in_array($choice["id"], $selectedChoiceIds)) {
                echo "<div class='choice poll-option'>";
                echo "<label>" . $choice["choicesName"] . " - 0%</label>";
                echo "<progress max='100' value='0'></progress></div>";
            } else {
                foreach ($rows as $row) {
                    if ($row['choicesId'] == $choice["id"]) {
                        echo "<div class='choice poll-option'>";
                        if ($userSelectedChoice["choicesId"] == $choice["id"])
                            echo "<label>" . $row["choicesName"] . " - " . round($row["choice_count"] / $sum, 3) * 100 . "% <i class='fa fa-check' aria-hidden='true'></i></label>";
                        else
                            echo "<label>" . $row["choicesName"] . " - " . round($row["choice_count"] / $sum, 3) * 100 . "%</label>";
                        echo "<progress max='100' value='" . round($row["choice_count"] / $sum, 3) * 100 . "'></progress></div>";
                        break;
                    }
                }
            }
        }
        exit();
    }
    echo '</div>';
    ?>
    <div class="poll-center">
        <form action="includes/vote.inc.php" method="POST">
            <?php
            echo "<h2 style='color:#66fcf1;'>" . $poll["pollsQues"] . "</h2>";
            if ($poll["pollsDesc"]) {
                echo "<p>" . $poll["pollsDesc"] . "</p><br>";
            }
            ?>
            <?php
            if (!empty($choices)) {
                foreach ($choices as $index => $choice) {
                    echo "<div class='choice poll-option'><input type='radio' required name='choice' value=" . $choice["id"] . " id=" . $index . ">";
                    echo "<label for=" . $index . ">" . $choice["choicesName"] . "</label></div>";
                }
                echo '<button class="submit-btn-center" type="submit" name="submit">Submit</button></br>';
                echo '<input type="hidden" name="poll" value=' . $id . '>';
            } else {
                echo "<p>Sorry no choices available!</p>";
            }
            ?>
        </form>
    </div>
</div>
<?php
if (isset($_GET["error"])) {
    $error = $_GET["error"];
    if ($error == "none") {
        echo "<div class='error-block'><p>Poll created successfully!<br>
        Send this link <a href='http://localhost/Polling-Booth/vote.php?id=".$id."'>http://localhost/Polling-Booth/vote.php?id=".$id."</a>&nbspto your desired audience!</p></div>";
    } else if ($error == "stmtfailed") {
        echo "<div class='error-block'><p>Oops! Something went wrong</p></div>";
    } else if ($error == "descUpdated") {
        echo "<div class='error-block'><p>Description updated!</p></div>";
    }
}

?>
</body>

</html>