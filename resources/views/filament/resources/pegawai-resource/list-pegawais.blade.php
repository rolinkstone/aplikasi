@extends('filament::layouts.app')

@section('content')
    <div>
        <h2>New Section Title</h2>
        <!-- Add your section content here -->
        <p>This is where your new section's content will go.</p>
    </div>

    <x-filament-tables::table
        :columns="$this->getTable()->getColumns()"
        :records="$this->getTableRecords()"
        :actions="$this->getActions()"
        :bulk-actions="$this->getBulkActions()"
    />
@endsection
