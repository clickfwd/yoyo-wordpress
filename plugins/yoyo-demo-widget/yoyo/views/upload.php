<form>  

    <div>
        <label for="photo">
            Photo
        </label>
        <div style="display: flex; align-items: center;">
            <span style="width: 4rem; height: 4rem; overflow: hidden;">
            <?php if ($this->image): ?>
                <div>
                    <img style="width: 4rem; height: 4rem; border-radius: 9999px;" src="data:image/jpg;base64,<?php echo $this->image; ?>" />
                </div>
            <?php else: ?>
                <svg style="color: #7e7e7e; width: 4rem; height: 4rem; border-radius: 9999px;" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            <?php endif; ?>
            </span>
            <span style="margin-left: 1.25rem;">
                <?php if ($this->image): ?>
                    <button yoyo:on="click">Refresh</button>
                <?php else: ?>              
                    <input name="photo" type="file" yoyo:post="render"  />
                <?php endif; ?>
            </span>
        </div>
    </div>  
                    
    <?php if ($this->error): ?>
    <div style="margin-top: 1rem; display: flex; align-items: center;">
        <svg style="width: 1.25rem; height: 1.25rem; color: red;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
        </svg>
        <span style="color: red; margin-left: .75rem;">
            <?php echo $this->error; ?>
        </span>
    </div>
    <?php endif; ?>
    
</form>