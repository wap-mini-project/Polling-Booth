<?php
    include_once "header.php";
    require_once "includes/dbh.inc.php";
    require_once "includes/functions.inc.php";

    if (!isset($_GET["id"]))
    {
        header("location: polls.php/error=errorloadingpoll");
        exit();
    }
    $id = (int)$_GET["id"];
    $poll = fetchPoll($conn, $id);        
    if (!$poll)
    {
        header("location: polls.php?error=polldoesntexist");
        exit();
    }
    $choices = fetchChoices($conn, $id);
?>
<div>
    <?php
        if (!isLoggedIn($_SESSION))
        {
            echo "<p>Oops, looks like you haven't logged in yet</p>";
            echo "Dont have an account yet? <a target='blank_' href='signup.php'>Sign Up</a><br>";
            echo "Have an account already? <a target='blank_' href='login.php'>Login In</a>";
        }
        else
        {
            echo '<div class="poll-center">';
            if (pollAnswered($conn, $_SESSION["userid"], $_GET["id"]) === true && pollExpired($poll) === true)
            { 
                echo "<h2 style='color:white;'>Poll answered - </h2>";
                $rows = number_of_choices($conn, $_GET["id"]);
                $sum = array_sum(array_column($rows, 'choice_count'));  
                $selectedChoiceIds = array_column($rows, 'choicesId');
                foreach($choices as $choice)
                {
                    if(!in_array($choice["id"], $selectedChoiceIds))
                    {
                        echo "<div class='choice poll-option'><div class='filler'>" . $choice["choicesName"] . " - 0%</div></div>";
                    }
                    else
                    {
                        foreach ($rows as $row) 
                        {
                            if ($row['choicesId'] == $choice["id"]) 
                            {
                                echo "<div class='choice poll-option'><div class='filler'>" . $row["choicesName"] . " - " . round($row["choice_count"]/$sum, 3) * 100 . "%</div></div>";
                                break;
                            }
                        }
                        
                    }
                }
                exit();
            }
            echo '</div>';
        }
    ?>
    <div class="poll-center" style="color:#66fcf1;">
        <form action="includes/vote.inc.php" method="POST">
            <?php 
                echo "<h2>".$poll["pollsQues"]."</h2>";
                if($poll["pollsDesc"])
                {
                    echo "<p>".$poll["pollsDesc"]."</p><br>";
                }
            ?>
            <div>
                <?php
                    if(!empty($choices))
                    {
                        foreach($choices as $index=>$choice)
                        {
                            echo "<div class='choice poll-option'><input type='radio' name='choice' value=" . $choice["id"] . " id=" . $index . ">";
                            echo "<label for=" . $index . ">" . $choice["choicesName"] . "</label></div>";
                        }
                        echo '<button class="submit-btn-center" type="submit" name="submit">Submit</button>';
                        echo '<input type="hidden" name="poll" value=' . $id .'>';
                    }
                    else
                    {
                        echo "<p>Sorry no choices available!</p>";
                    }
                    if (isset($_GET["error"]))
                    {
                        $error = $_GET["error"];
                        if($error == "none")
                        {
                            echo "<p>Poll created successfully!</p>";
                        }
                        else if($error == "stmtfailed")
                        {
                            echo "<p>Oops! Something went wrong</p>";
                        }
                    }
                ?>
            </div>
        </form>
    </div>
</div>