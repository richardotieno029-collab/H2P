<!-- GLOBAL LOADER -->
<div id="loadingBox" class="loading-overlay">
    <div class="loading-content">
        <div class="spinner"></div>
        <p id="loadingText">Processing...</p>
    </div>
</div>

<style>
.loading-overlay{
    display:none;
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.4);
    z-index:9999;
    align-items:center;
    justify-content:center;
}

.loading-content{
    text-align:center;
    color:white;
    font-size:18px;
}

.spinner{
    width:45px;
    height:45px;
    border:5px solid #ccc;
    border-top:5px solid #00bcd4;
    border-radius:50%;
    animation: spin 1s linear infinite;
    margin:0 auto 10px;
}

.file-count {
    font-size: 0.85rem;
    color: #666;
    margin-top: 6px;
}

.file-count--error {
    color: #d9534f;
}

@keyframes spin{
    0%{ transform: rotate(0deg); }
    100%{ transform: rotate(360deg); }
}
</style>

<script>
function showLoading(message){
    document.getElementById("loadingText").innerText = message;
    document.getElementById("loadingBox").style.display = "flex";
}

function showClientToast(type, message) {
    const existing = document.querySelector('.toast');
    if (existing) {
        existing.remove();
    }
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('hide');
    }, 3500);
}

function initFileCountIndicators() {
    const fileInputs = document.querySelectorAll('input[type="file"][data-max-files]');
    fileInputs.forEach((input) => {
        const maxFiles = parseInt(input.dataset.maxFiles, 10);
        if (!maxFiles) return;

        const counter = document.createElement('div');
        counter.className = 'file-count';
        counter.textContent = `0/${maxFiles} selected`;
        input.insertAdjacentElement('afterend', counter);

        const update = () => {
            const count = input.files ? input.files.length : 0;
            counter.textContent = `${count}/${maxFiles} selected`;
            counter.classList.toggle('file-count--error', count > maxFiles);
        };

        input.addEventListener('change', update);
        update();
    });
}

function validateFileInputs(form) {
    const fileInputs = form.querySelectorAll('input[type="file"]');

    for (const input of fileInputs) {
        const maxFiles = parseInt(input.dataset.maxFiles || '', 10);
        const maxSize = parseInt(input.dataset.maxSize || '', 10);

        if (!input.files) continue;

        if (maxFiles && input.files.length > maxFiles) {
            showClientToast('error', `Please select at most ${maxFiles} file(s) for "${input.name}".`);
            input.focus();
            return false;
        }

        if (maxSize) {
            for (const file of input.files) {
                if (file.size > maxSize) {
                    const mb = (maxSize / (1024 * 1024)).toFixed(1);
                    showClientToast('error', `Each file must be <= ${mb}MB for "${input.name}".`);
                    input.focus();
                    return false;
                }
            }
        }
    }

    return true;
}

function handleSubmit(form, message){
    if (!validateFileInputs(form)) {
        return false;
    }

    showLoading(message);

    const btn = form.querySelector("button[type='submit'], input[type='submit']");
    if (btn) {
        btn.disabled = true;
        if (btn.tagName.toLowerCase() === 'button') {
            btn.innerText = "Please wait...";
        } else if (btn.tagName.toLowerCase() === 'input') {
            btn.value = "Please wait...";
        }
    }

    return true;
}

document.addEventListener('DOMContentLoaded', () => {
    initFileCountIndicators();
});
</script>