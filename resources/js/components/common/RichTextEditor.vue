<template>
    <div ref="editorWrapper" class="rich-text-editor-wrapper">
        <div ref="editorContainer" class="rich-text-editor"></div>
    </div>
</template>

<script>
import Quill from 'quill';
import 'quill/dist/quill.snow.css';

export default {
    name: 'RichTextEditor',
    props: {
        modelValue: {
            type: String,
            default: ''
        },
        placeholder: {
            type: String,
            default: 'Enter content...'
        },
        disabled: {
            type: Boolean,
            default: false
        },
        // Control when editor should be active/visible
        active: {
            type: Boolean,
            default: true
        }
    },
    emits: ['update:modelValue'],
    data() {
        return {
            quillEditor: null,
            isInitialized: false
        };
    },
    watch: {
        modelValue(newVal) {
            // Update editor content if it changed externally
            if (this.quillEditor && this.quillEditor.root.innerHTML !== newVal) {
                this.quillEditor.clipboard.dangerouslyPasteHTML(newVal || '');
            }
        },
        active(newVal, oldVal) {
            if (newVal && !oldVal) {
                // Becoming active - initialize if not already initialized
                if (!this.isInitialized) {
                    this.$nextTick(() => {
                        this.initEditor();
                    });
                }
            } else if (!newVal && oldVal) {
                // Becoming inactive - destroy editor
                this.destroyEditor();
            }
        },
        disabled(newVal) {
            if (this.quillEditor) {
                this.quillEditor.enable(!newVal);
            }
        }
    },
    mounted() {
        if (this.active) {
            this.$nextTick(() => {
                this.initEditor();
            });
        }
    },
    beforeUnmount() {
        this.destroyEditor();
    },
    methods: {
        initEditor() {
            // Prevent multiple initializations
            if (this.isInitialized || this.quillEditor) {
                return;
            }

            const container = this.$refs.editorContainer;
            if (!container) {
                return;
            }

            // Ensure we start from a clean slate (toolbar lives outside container)
            this.cleanupQuillDom();

            try {
                // Clear container
                container.innerHTML = '';

                // Create Quill instance
                this.quillEditor = new Quill(container, {
                    theme: 'snow',
                    modules: {
                        toolbar: [
                            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ 'color': [] }, { 'background': [] }],
                            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                            [{ 'align': [] }],
                            ['link', 'image'],
                            ['blockquote', 'code-block'],
                            ['clean']
                        ]
                    },
                    placeholder: this.placeholder,
                    readOnly: this.disabled
                });

                // Set initial content
                if (this.modelValue) {
                    this.quillEditor.clipboard.dangerouslyPasteHTML(this.modelValue);
                } else {
                    this.quillEditor.setText('');
                }

                // Listen for content changes
                this.quillEditor.on('text-change', () => {
                    const content = this.quillEditor.root.innerHTML;
                    this.$emit('update:modelValue', content);
                });

                this.isInitialized = true;
            } catch (error) {
                console.error('Error initializing Quill editor:', error);
                this.quillEditor = null;
                this.isInitialized = false;
            }
        },
        destroyEditor() {
            if (this.quillEditor) {
                // Clear editor instance and DOM that Quill adds
                this.quillEditor = null;
            }

            this.cleanupQuillDom();
            this.isInitialized = false;
        },
        // Remove generated toolbar and reset container so re-inits start clean
        cleanupQuillDom() {
            const container = this.$refs.editorContainer;
            const wrapper = this.$refs.editorWrapper || (container ? container.parentElement : null);

            if (wrapper) {
                const toolbars = wrapper.querySelectorAll('.ql-toolbar');
                toolbars.forEach(toolbar => toolbar.remove());
            }

            if (container) {
                container.innerHTML = '';
                container.className = 'rich-text-editor';
            }
        },
        // Public method to get content
        getContent() {
            return this.quillEditor ? this.quillEditor.root.innerHTML : this.modelValue;
        },
        // Public method to set content
        setContent(content) {
            if (this.quillEditor) {
                this.quillEditor.root.innerHTML = content || '';
                this.$emit('update:modelValue', content || '');
            }
        },
        // Public method to focus editor
        focus() {
            if (this.quillEditor) {
                this.quillEditor.focus();
            }
        }
    }
};
</script>

<style scoped>
.rich-text-editor-wrapper {
    border: 1px solid rgba(0, 0, 0, 0.12);
    border-radius: 4px;
    overflow: hidden;
}

.rich-text-editor {
    min-height: 400px;
}

/* Ensure Quill styles are applied */
:deep(.ql-container) {
    font-family: inherit;
    font-size: inherit;
}

:deep(.ql-editor) {
    min-height: 400px;
}
</style>

