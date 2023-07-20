<?php



\defined('ABSPATH') or 'Plugin file cannot be accessed directly.';
?>
<style>
    @font-face {
        font-family: AvertaStd-Regular;
        src: url("<?php 
echo plugin_dir_url(\ASTRAROOT) . "gk/assets/fonts/AvertaStd-Regular.otf";
?>") format("opentype");
    }

    * {
        box-sizing: border-box;
        padding: 0;
        margin: 0;
        font-family: 'AvertaStd-Regular','Arial';
    }

    body2 {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        padding: 4vh 15%;
        background: #EDF0F5;
    }

    .white-bg {
        width: 91%;
        height: 82vh;
        background: #fff;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 1.5em;
        border-radius: 22px;
        margin: 30px 50px;
    }

    .wrapper {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: space-evenly;
    }

    .header {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .logo-container {
        width: 100px;
    }

    .header-text p {
        font-size: 20px;
        margin-bottom: 0.4rem;
    }

    .steps-container {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        width: 85%;
        margin: 28px 0;
    }

    .step {
        border-radius: 12px;
        padding: 20px 30px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        gap: 10px;
        box-shadow: rgb(149 157 165 / 15%) 0px 8px 24px;
        height: 345px;
    }

    .step .step-header {
        text-align: center;
        font-size: 24px;
        font-weight: 600;
        margin-top: 0;
    }

    .step img {
        width: 120px;
    }

    .step p {
        font-size: 16px;
        margin-bottom: 0.4rem;
    }

    .step1-lower-section {
        display: flex;
        flex-direction: column;
    }

    .step1-lower-section .key-prompt {
        font-size: 10px;
    }

    .step1-btn {
        background-color: #3176F8;
        padding: 8px 18px;
        border: 0;
        color: #fff;
        font-size: 16px;
        border-radius: 4px;
        cursor: pointer;
        text-decoration: none;
        margin: 10px 0 5px;
        align-self: center;
    }

    .step1-btn:hover {
        color: #fff
    }

    .key-prompt {
        font-size: 12px;
    }

    .connect-btn {
        background-color: #3176F8;
        padding: 16px 36px;
        border: 0;
        color: #fff;
        font-size: 22px;
        border-radius: 4px;
        cursor: pointer;
        text-decoration: none;
    }

    .connect-btn:hover {
        color: #fff
    }

    .footer-section {
        display: flex;
        gap: 10px;
        flex-direction: column;
        align-items: center;
    }
</style>
<div class="white-bg">
    <div class="wrapper">
        <div class="header">
            <div class="logo-container">
                <img src="<?php 
echo plugin_dir_url(__DIR__) . '../../assets/images/astra-logo.svg';
?>" alt="Astra Security Logo" classname="astra-logo" width="100px">
            </div>

            <div class="header-text">
                <p>Hey there! 👋 Let’s get you started real quick, follow the steps below.</p>
            </div>
        </div>

        <div class="steps-container">
            <div class="step">
                <h3 class="step-header">Step 1</h3>
                <img src="<?php 
echo plugin_dir_url(__DIR__) . '../../assets/images/logos/sign-in.png';
?>" alt="icon">
                <div class="step1-lower-section">
                    <p>Choose the right plan for you from our website</p>
                    <a href="https://www.getastra.com/pricing" class="step1-btn">
                        Get Started
                    </a>
                </div>
            </div>
            <div class="step">
                <h3 class="step-header">Step 2</h3>
                <img src="<?php 
echo plugin_dir_url(__DIR__) . '../../assets/images/logos/clipboard-text.png';
?>" alt="icon">
                <p>You’ll see an activation code in your Astra Dashboard, copy it</p>
            </div>
            <div class="step">
                <h3 class="step-header">Step 3</h3>
                <img src="<?php 
echo plugin_dir_url(__DIR__) . '../../assets/images/logos/hand-pointing.png';
?>" alt="icon">
                <p>Click on the 'Enter Activation Code' button below and you're done!</p>
            </div>
        </div>


        <div class="footer-section">
            <a href="<?php 
echo get_site_url() . '/?astraRoute=api/login';
?>" class="connect-btn" target="_blank">Enter Activation Code</a>
            <span class="key-prompt">Already have the activation code? Simply press the button above</span>
        </div>

    </div>
</div><?php 
