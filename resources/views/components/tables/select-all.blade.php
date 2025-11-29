<div x-data="selectAll('{{ $key }}')">
    <flux:checkbox x-on:change="handleCheck" x-ref="checkbox" />
</div>

@script
<script>
    Alpine.data('selectAll', (key) => {
        return {
            init() {
                this.$wire.$watch('selectedItems', () => {
                    this.updateCheckAllState()
                })

                this.$wire.$watch(key, () => {
                    this.updateCheckAllState()
                })
            },

            updateCheckAllState() {
                if (this.pageIsSelected()) {
                    this.$refs.checkbox.checked = true
                    this.$refs.checkbox.indeterminate = false
                } else if (this.pageIsEmpty()) {
                    this.$refs.checkbox.checked = false
                    this.$refs.checkbox.indeterminate = false
                } else {
                    this.$refs.checkbox.checked = false
                    this.$refs.checkbox.indeterminate = true
                }
            },

            pageIsSelected() {
                return this.$wire[key].every(id => this.$wire.selectedItems.includes(id))
            },

            pageIsEmpty() {
                return this.$wire.selectedItems.length === 0
            },

            selectAll() {
                this.$wire[key]
                    .filter(id => !this.$wire.selectedItems.includes(id))
                    .forEach(id => this.$wire.selectedItems.push(id));
            },

            deselectAll() {
                this.$wire.selectedItems = [];
            },

            handleCheck(e) {
                e.target.checked ? this.selectAll() : this.deselectAll();
            }
        }
    })
</script>
@endscript
