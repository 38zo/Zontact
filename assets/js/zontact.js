(function(){
	function qs(s, r){return (r||document).querySelector(s)}
	function qsa(s, r){return (r||document).querySelectorAll(s)}
	
	let focusableElements = [];
	let firstFocusable, lastFocusable;
	
	function openModal(root, modal){
		modal.setAttribute('aria-hidden','false');
		qs('.zontact-button', root).setAttribute('aria-expanded','true');
		document.body.style.overflow='hidden';
		
		// Focus trap setup
		focusableElements = qsa('input,textarea,button,[tabindex]:not([tabindex="-1"])', modal);
		firstFocusable = focusableElements[0];
		lastFocusable = focusableElements[focusableElements.length - 1];
		
		// Focus first element
		if(firstFocusable) firstFocusable.focus();
	}
	
	function closeModal(root, modal){
		modal.setAttribute('aria-hidden','true');
		qs('.zontact-button', root).setAttribute('aria-expanded','false');
		document.body.style.overflow='';
		focusableElements = [];
	}
	
	function handleTabKey(e, modal){
		if(e.key !== 'Tab') return;
		
		if(e.shiftKey){
			if(document.activeElement === firstFocusable){
				e.preventDefault();
				lastFocusable.focus();
			}
		} else {
			if(document.activeElement === lastFocusable){
				e.preventDefault();
				firstFocusable.focus();
			}
		}
	}
	
	function serializeForm(form){
		const data = new FormData(form);
		data.append('action','zontact_submit');
		data.append('nonce', (window.zontact && zontact.nonce) || '');
		return data;
	}
	
	function setStatus(el, msg, type = ''){
		el.textContent = msg || '';
		el.className = 'zontact-status' + (type ? ' ' + type : '');
	}
	
	function setSubmitState(btn, disabled){
		btn.disabled = disabled;
		btn.textContent = disabled ? 
			((zontact && zontact.strings && zontact.strings.sending) || 'Sending…') : 
			'Send';
	}

	document.addEventListener('DOMContentLoaded', function(){
		const root = qs('.zontact-root'); if(!root) return;
		const modal = qs('#zontact-modal', root);
		const openBtn = qs('.zontact-button', root);
		const closeBtns = qsa('[data-zontact-close]', root);
		const form = qs('.zontact-form', root);
		const statusEl = qs('.zontact-status', root);
		const submitBtn = qs('.zontact-submit', root);

		openBtn.addEventListener('click', function(){ openModal(root, modal); });
		closeBtns.forEach(function(btn){ btn.addEventListener('click', function(){ closeModal(root, modal); }); });
		
		// Close on escape
		document.addEventListener('keydown', function(e){ 
			if(e.key==='Escape' && modal.getAttribute('aria-hidden') === 'false'){ 
				closeModal(root, modal); 
			} 
		});
		
		// Focus trap
		modal.addEventListener('keydown', function(e){
			if(modal.getAttribute('aria-hidden') === 'false'){
				handleTabKey(e, modal);
			}
		});

		form.addEventListener('submit', function(e){
			e.preventDefault();
			setSubmitState(submitBtn, true);
			setStatus(statusEl, (zontact && zontact.strings && zontact.strings.sending) || 'Sending…');
			
			const data = serializeForm(form);
			fetch((zontact && zontact.ajax_url) || '/wp-admin/admin-ajax.php', { 
				method:'POST', 
				body:data, 
				credentials:'same-origin' 
			})
			.then(function(res){ return res.json(); })
			.then(function(json){
				if(json && json.success){
					setStatus(statusEl, (zontact && zontact.strings && zontact.strings.success) || 'Sent.', 'success');
					form.reset();
					setTimeout(function(){ 
						closeModal(root, modal); 
						setStatus(statusEl,''); 
						setSubmitState(submitBtn, false);
					}, 1500);
				}else{
					setStatus(statusEl, (zontact && zontact.strings && zontact.strings.error) || 'There was an error.', 'error');
					setSubmitState(submitBtn, false);
				}
			})
			.catch(function(){ 
				setStatus(statusEl, (zontact && zontact.strings && zontact.strings.error) || 'There was an error.', 'error');
				setSubmitState(submitBtn, false);
			});
		});
	});
})();