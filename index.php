<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Design from Image</title>
    <meta name="description" content="Upload your room photo and let AI redesign it with a new interior design style.">

    <style>

        /* ===========================
           GENERAL PAGE STYLES
        =========================== */

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f0f1fa;
            min-height: 100vh;
            padding: 30px 20px;
        }

        /* ===========================
           BACK BUTTON (top left)
        =========================== */

        .back-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: white;
            cursor: pointer;
            font-size: 18px;
            color: #444;
            text-decoration: none;
            margin-bottom: 20px;
        }

        .back-btn:hover {
            background: #f5f5f5;
        }

        /* ===========================
           PAGE TITLE
        =========================== */

        .page-title {
            text-align: center;
            font-size: 26px;
            font-weight: bold;
            color: #1a1a2e;
            margin-bottom: 8px;
        }

        .page-subtitle {
            text-align: center;
            color: #888;
            font-size: 14px;
            margin-bottom: 30px;
        }

        /* ===========================
           MAIN LAYOUT — two columns
        =========================== */

        .main-layout {
            display: flex;
            gap: 20px;
            max-width: 1100px;
            margin: 0 auto;
            flex-wrap: wrap;
        }

        /* LEFT COLUMN */
        .left-panel {
            background: white;
            border-radius: 16px;
            padding: 25px;
            width: 310px;
            min-width: 280px;
            flex-shrink: 0;
        }

        /* RIGHT COLUMN */
        .right-panel {
            background: white;
            border-radius: 16px;
            padding: 25px;
            flex: 1;
            min-width: 300px;
        }

        /* ===========================
           SECTION HEADINGS
        =========================== */

        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #1a1a2e;
            margin-bottom: 15px;
        }

        /* ===========================
           IMAGE UPLOAD BOX
        =========================== */

        .upload-box {
            border: 1.5px dashed #ccc;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            position: relative;
            background: #fafafa;
            margin-bottom: 12px;
        }

        .upload-box:hover {
            border-color: #6c5ce7;
            background: #f5f3ff;
        }

        /* Hidden file input — click anywhere on the box triggers it */
        #roomImage {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .upload-icon {
            font-size: 40px;
            color: #6c5ce7;
            margin-bottom: 10px;
        }

        .upload-text {
            color: #777;
            font-size: 14px;
        }

        /* Preview of uploaded image */
        #uploadedImage {
            width: 100%;
            border-radius: 10px;
            display: none;
            margin-bottom: 10px;
        }

        /* X button to clear the image */
        .remove-image-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 50%;
            width: 26px;
            height: 26px;
            cursor: pointer;
            font-size: 14px;
            display: none;
            align-items: center;
            justify-content: center;
            color: #555;
        }

        /* Change Image button */
        .change-image-btn {
            width: 100%;
            padding: 10px;
            border: 1.5px solid #6c5ce7;
            border-radius: 8px;
            background: white;
            color: #6c5ce7;
            font-size: 14px;
            cursor: pointer;
            margin-bottom: 20px;
        }

        .change-image-btn:hover {
            background: #f5f3ff;
        }

        /* ===========================
           DESCRIBE DESIGN TEXTAREA
        =========================== */

        #promptInput {
            width: 100%;
            height: 90px;
            border: 1.5px solid #ddd;
            border-radius: 10px;
            padding: 12px;
            font-size: 14px;
            resize: none;
            color: #333;
            font-family: Arial, sans-serif;
            margin-bottom: 15px;
        }

        #promptInput:focus {
            outline: none;
            border-color: #6c5ce7;
        }

        /* ===========================
           GENERATE DESIGN BUTTON
        =========================== */

        #generateBtn {
            width: 100%;
            padding: 14px;
            background: #6c5ce7;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 10px;
        }

        #generateBtn:hover {
            background: #5a4bd1;
        }

        #generateBtn:disabled {
            background: #aaa;
            cursor: not-allowed;
        }

        /* ===========================
           ERROR AND SUCCESS MESSAGES
        =========================== */

        #errorMsg {
            color: #e74c3c;
            font-size: 13px;
            margin-bottom: 15px;
            display: none;
            background: #fff5f5;
            border: 1px solid #fcc;
            border-radius: 8px;
            padding: 10px 12px;
        }

        /* ===========================
           QUICK PROMPT CHIPS
        =========================== */

        .quick-prompts-title {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .chips {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .chip {
            padding: 7px 14px;
            border: 1.5px solid #ccc;
            border-radius: 20px;
            font-size: 13px;
            cursor: pointer;
            color: #444;
            background: white;
        }

        .chip:hover {
            border-color: #6c5ce7;
            color: #6c5ce7;
            background: #f5f3ff;
        }

        /* ===========================
           RIGHT PANEL — TOP BAR
        =========================== */

        .result-topbar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .result-topbar-left h3 {
            font-size: 16px;
            font-weight: bold;
            color: #1a1a2e;
        }

        .result-topbar-left p {
            font-size: 13px;
            color: #888;
            margin-top: 2px;
        }

        /* Action buttons — Regenerate, Download, Save */
        .action-btns {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            padding: 8px 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: white;
            font-size: 13px;
            cursor: pointer;
            color: #444;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .action-btn:hover {
            background: #f5f5f5;
            border-color: #bbb;
        }

        .action-btn:disabled {
            color: #aaa;
            cursor: not-allowed;
        }

        /* ===========================
           AI RESULT AREA
           — single container holds
             placeholder / spinner /
             generated image all in
             the same fixed box
        =========================== */

        .result-box {
            position: relative;
            width: 100%;
            min-height: 350px;
            background: #f0f1fa;
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Placeholder shown before design is generated */
        #resultPlaceholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 20px;
        }

        .placeholder-icon {
            font-size: 70px;
            margin-bottom: 15px;
            color: #bbb;
        }

        .placeholder-title {
            font-size: 16px;
            font-weight: bold;
            color: #555;
            margin-bottom: 6px;
        }

        .placeholder-sub {
            font-size: 13px;
            color: #999;
        }

        /* Loading Spinner — hidden by default */
        #loadingArea {
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .spinner {
            width: 48px;
            height: 48px;
            border: 5px solid #ddd;
            border-top-color: #6c5ce7;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .loading-text {
            color: #666;
            font-size: 14px;
            text-align: center;
            max-width: 260px;
        }

        /* The AI Generated Image — fills the result box */
        #resultImage {
            display: none;
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 12px;
        }

        /* Success message for Save */
        #successMsg {
            color: #27ae60;
            font-size: 13px;
            margin-top: 10px;
            display: none;
            text-align: center;
        }

    </style>

</head>


<body>


    <!-- Back Button -->
    <a href="#" class="back-btn" onclick="history.back()">&#8592;</a>


    <!-- Page Title -->
    <h1 class="page-title">AI design from Image</h1>
    <p class="page-subtitle">Upload your room image, add prompt and generate new design</p>


    <!-- ===================================
         MAIN LAYOUT
    =================================== -->

    <div class="main-layout">


        <!-- ===================================
             LEFT PANEL
        =================================== -->

        <div class="left-panel">


            <!-- STEP 1: Upload Image -->
            <p class="section-title">1. Upload Image</p>

            <!-- Upload Box -->
            <div class="upload-box" id="uploadBox">

                <!-- Hidden file input -->
                <input
                    type="file"
                    id="roomImage"
                    accept="image/jpeg,image/png,image/webp"
                >

                <!-- Remove image X button -->
                <button class="remove-image-btn" id="removeBtn" onclick="removeImage(event)">&#x2715;</button>

                <!-- Placeholder icon + text (shown before upload) -->
                <div id="uploadPlaceholder">
                    <div class="upload-icon">&#9729;</div>
                    <p class="upload-text">Click to upload image</p>
                </div>

                <!-- Preview of selected image -->
                <img id="uploadedImage" alt="Uploaded Room">

            </div>


            <!-- Change Image Button -->
            <button class="change-image-btn" onclick="document.getElementById('roomImage').click()">
                &#128247; Change Image
            </button>


            <!-- STEP 2: Describe Your Design -->
            <p class="section-title">2. Describe your design</p>

            <textarea
                id="promptInput"
                placeholder="e.g. Modern style with wooden furniture and warm lighting..."
            ></textarea>


            <!-- Generate Design Button -->
            <button id="generateBtn" onclick="generateDesign()">
                ✨ Generate Design
            </button>


            <!-- Error Message -->
            <div id="errorMsg"></div>


            <!-- Quick Prompts -->
            <p class="quick-prompts-title">Quick Prompt</p>

            <div class="chips">
                <button class="chip" onclick="addChip('Modern Style')">Modern Style</button>
                <button class="chip" onclick="addChip('Minimal Design')">Minimal Design</button>
                <button class="chip" onclick="addChip('Luxury Look')">Luxury Look</button>
                <button class="chip" onclick="addChip('Wooden Theme')">Wooden Theme</button>
                <button class="chip" onclick="addChip('Add Plants')">Add Plants</button>
                <button class="chip" onclick="addChip('Warm Lighting')">Warm Lighting</button>
            </div>


        </div>


        <!-- ===================================
             RIGHT PANEL
        =================================== -->

        <div class="right-panel">


            <!-- Top Bar -->
            <div class="result-topbar">

                <div class="result-topbar-left">
                    <h3>3. AI Generated Design</h3>
                    <p>Here is your AI generated room design</p>
                </div>

                <!-- Action Buttons -->
                <div class="action-btns">

                    <button class="action-btn" id="regenBtn" onclick="regenerate()" disabled>
                        &#8635; Regenerate
                    </button>

                    <button class="action-btn" id="downloadBtn" onclick="downloadImage()" disabled>
                        &#8659; Download
                    </button>

                    <button class="action-btn" id="saveBtn" onclick="saveDesign()" disabled>
                        &#9825; Save
                    </button>

                </div>

            </div>


            <!-- ===========================
                 RESULT BOX
                 Single container — shows one
                 of: placeholder / spinner / image
            =========================== -->
            <div class="result-box" id="resultBox">

                <!-- Placeholder (before generation) -->
                <div id="resultPlaceholder">
                    <div class="placeholder-icon">&#128444;</div>
                    <p class="placeholder-title">AI Design will appear here</p>
                    <p class="placeholder-sub">Upload an image, add a prompt and click Generate Design</p>
                </div>

                <!-- Loading Spinner -->
                <div id="loadingArea">
                    <div class="spinner"></div>
                    <p class="loading-text">AI is generating your interior design...<br>This may take 20–40 seconds.</p>
                </div>

                <!-- The AI Generated Image (shown inside the box) -->
                <img id="resultImage" alt="AI Generated Interior Design">

            </div>


            <!-- Success message for Save -->
            <div id="successMsg">&#10003; Design saved successfully!</div>


        </div>


    </div>


    <!-- ===================================
         JAVASCRIPT
    =================================== -->

    <script>


        // ===================================
        // VARIABLES we need to remember
        // ===================================

        let lastUploadedFile  = null;   // Last uploaded image file (for regeneration)
        let lastPrompt        = "";     // Last prompt used (for regeneration)
        let currentDesignId   = null;   // Design ID returned from backend
        let currentGeneratedImage = ""; // Path of generated image (for download)


        // ===================================
        // IMAGE UPLOAD PREVIEW
        // ===================================

        document.getElementById("roomImage").addEventListener("change", function () {

            const file = this.files[0];
            if (!file) return;

            lastUploadedFile = file;

            const reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById("uploadedImage").src = e.target.result;
                document.getElementById("uploadedImage").style.display = "block";
                document.getElementById("uploadPlaceholder").style.display = "none";
                document.getElementById("removeBtn").style.display = "flex";
            };
            reader.readAsDataURL(file);
        });


        // ===================================
        // REMOVE UPLOADED IMAGE
        // ===================================

        function removeImage(event) {
            event.stopPropagation();

            document.getElementById("roomImage").value = "";
            document.getElementById("uploadedImage").style.display = "none";
            document.getElementById("uploadPlaceholder").style.display = "block";
            document.getElementById("removeBtn").style.display = "none";
            lastUploadedFile = null;
        }


        // ===================================
        // QUICK PROMPT CHIP CLICKED
        // ===================================

        function addChip(text) {
            const textarea = document.getElementById("promptInput");
            textarea.value = textarea.value.trim() === "" ? text : textarea.value + ", " + text;
        }


        // ===================================
        // GENERATE DESIGN
        // ===================================

        async function generateDesign() {

            const fileInput = document.getElementById("roomImage");

            if (!fileInput.files[0] && !lastUploadedFile) {
                showError("Please upload a room image first.");
                return;
            }

            const prompt     = document.getElementById("promptInput").value.trim();
            lastUploadedFile = fileInput.files[0] || lastUploadedFile;
            lastPrompt       = prompt;

            await callGenerateAPI(lastUploadedFile, lastPrompt);
        }


        // ===================================
        // REGENERATE (uses last image + prompt)
        // ===================================

        async function regenerate() {
            if (!lastUploadedFile) { showError("No image to regenerate with."); return; }
            await callGenerateAPI(lastUploadedFile, lastPrompt);
        }


        // ===================================
        // ACTUAL API CALL FUNCTION
        // ===================================

        async function callGenerateAPI(file, prompt) {

            const formData = new FormData();
            formData.append("room_image", file);
            formData.append("prompt", prompt);

            showState("loading");
            hideError();
            hideSuccess();
            disableActionButtons(true);
            document.getElementById("generateBtn").disabled    = true;
            document.getElementById("generateBtn").textContent = "Generating...";

            try {

                const response = await fetch("generate_design.php", {
                    method: "POST",
                    body: formData
                });

                const data = await response.json();
                console.log("Server response:", data);

                if (!data.success) {
                    showError(data.message);
                    showState("placeholder");
                    return;
                }

                currentDesignId       = data.design_id;
                currentGeneratedImage = data.generated_image;

                // Show the generated image INSIDE the result-box
                const img = document.getElementById("resultImage");
                img.src   = data.generated_image + "?t=" + Date.now();
                showState("image");

                disableActionButtons(false);

            } catch (error) {
                console.error(error);
                showError("Something went wrong. Please try again.");
                showState("placeholder");

            } finally {
                document.getElementById("generateBtn").disabled    = false;
                document.getElementById("generateBtn").textContent = "✨ Generate Design";
            }
        }


        // ===================================
        // SHOW ONE STATE IN THE RESULT BOX
        // ===================================
        // state = "placeholder" | "loading" | "image"

        function showState(state) {
            document.getElementById("resultPlaceholder").style.display = state === "placeholder" ? "flex"  : "none";
            document.getElementById("loadingArea").style.display        = state === "loading"     ? "flex"  : "none";
            document.getElementById("resultImage").style.display        = state === "image"       ? "block" : "none";
        }


        // ===================================
        // DOWNLOAD IMAGE
        // ===================================

        function downloadImage() {
            if (!currentGeneratedImage) return;
            const link    = document.createElement("a");
            link.href     = currentGeneratedImage;
            link.download = "ai_interior_design.png";
            link.click();
        }


        // ===================================
        // SAVE DESIGN TO DATABASE
        // ===================================

        async function saveDesign() {

            if (!currentDesignId) return;

            try {

                const formData = new FormData();
                formData.append("design_id", currentDesignId);

                const response = await fetch("save_design.php", {
                    method: "POST",
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showSuccess("Design saved successfully!");
                    document.getElementById("saveBtn").textContent = "✓ Saved";
                    document.getElementById("saveBtn").disabled    = true;
                } else {
                    showError(data.message);
                }

            } catch (error) {
                showError("Could not save. Please try again.");
            }
        }


        // ===================================
        // HELPER FUNCTIONS
        // ===================================

        function disableActionButtons(disabled) {
            document.getElementById("regenBtn").disabled    = disabled;
            document.getElementById("downloadBtn").disabled = disabled;
            document.getElementById("saveBtn").disabled     = disabled;
        }

        function showError(msg) {
            const el = document.getElementById("errorMsg");
            el.textContent   = msg;
            el.style.display = "block";
        }

        function hideError() {
            document.getElementById("errorMsg").style.display = "none";
        }

        function showSuccess(msg) {
            const el = document.getElementById("successMsg");
            el.textContent   = msg;
            el.style.display = "block";
        }

        function hideSuccess() {
            document.getElementById("successMsg").style.display = "none";
        }


    </script>


</body>

</html>